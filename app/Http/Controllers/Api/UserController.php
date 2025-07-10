<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignSubscription(Request $request, User $user): JsonResponse
    {
        // 1. Validate Input (based on US-07 Fields Description and Business Rules)
        try {
            $validatedData = $request->validate([
                'subscription_plan_id' => [
                    'required',
                    'integer',
                    // Only allow active plans (Business Rule 2 from US-06)
                    Rule::exists('subscription_plans', 'id')->where(function ($query) {
                        $query->where('is_active', true);
                    }),
                ],
                'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'status' => ['nullable', Rule::in(['active', 'pending', 'cancelled', 'expired', 'trial'])], // US-07 Status options

                // Override fields (Admin can change - US-07 Fields Description)
                'device_limit' => ['nullable', 'integer', 'min:0'],
                'user_limit' => ['nullable', 'integer', 'min:0'],
                'max_dashboards' => ['nullable', 'integer', 'min:0'],
                'custom_widgets' => ['nullable', 'integer', 'min:0'],
            ], [
                'subscription_plan_id.required' => __('MSG-18'), // Please fill all required fields.
                'subscription_plan_id.exists' => __('MSG-17'), // Please add valid data.
                'min' => __('MSG-17'),
                'date' => __('MSG-17'),
                'before_or_equal' => __('MSG-17'),
                'after_or_equal' => __('MSG-17'),
                'in' => __('MSG-17'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('MSG-17'), // Please add valid data.
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        }

        DB::beginTransaction();
        try {
            $plan = SubscriptionPlan::findOrFail($validatedData['subscription_plan_id']);

            // Business Rule 1: Each user/tenant must have one and only one active subscription tier.
            // Deactivate any existing active subscriptions for this user.
            $user->userSubscriptions()->where('status', 'active')->update(['status' => 'expired']);

            // Prepare effective limits, potentially overriding plan defaults
            $effectiveLimits = [
                'effective_device_limit' => $validatedData['device_limit'] ?? null,
                'effective_user_limit' => $validatedData['user_limit'] ?? null,
                'effective_max_dashboards' => $validatedData['max_dashboards'] ?? null,
                'effective_custom_widgets' => $validatedData['custom_widgets'] ?? null,
            ];

            // Determine start and end dates (Business Rule 4, BR-20)
            $startDate = $validatedData['start_date'] ?? now();
            $endDate = $validatedData['end_date'];

            // Calculate end date based on plan duration if not manually provided by admin
            if (!$endDate && $plan->duration_days > 0) {
                $endDate = (clone $startDate)->addDays($plan->duration_days);
                // If it's a default trial plan (BR-5 from US-05), use trial_period_days instead
                if ($plan->is_default && $plan->trial_period_days > 0) {
                    $endDate = (clone $startDate)->addDays($plan->trial_period_days);
                }
            }


            // Alternative Scenario 3: Warning if new plan has fewer allowed devices than currently assigned
            // This requires knowing the user's *current actual usage*, which is typically dynamic.
            // For an API, we can send a warning back if the admin *explicitly* set a lower limit.
            // A more robust check would involve querying the user's actual device count.
            $currentEffectiveDeviceLimit = $user->currentActiveSubscription ? $user->currentActiveSubscription->device_limit : 0; // Get current limit if active subscription exists
            if ($validatedData['device_limit'] && $validatedData['device_limit'] < $currentEffectiveDeviceLimit) {
                // This is a warning for the frontend, not a hard block for the API.
                // The frontend should display MSG-27 if this condition is met.
                // For now, we'll log it and let the API response contain the actual new limit.
                \Log::warning("Admin assigning lower device limit to user {$user->id}. New limit: {$validatedData['device_limit']}, Previous effective limit: {$currentEffectiveDeviceLimit}.");
            }


            // Create the new user subscription record
            $userSubscription = $user->userSubscriptions()->create(array_merge([
                'subscription_plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $validatedData['status'] ?? 'active', // Default to active if not provided
            ], $effectiveLimits)); // Pass effectiveLimits to be saved as nullable overrides


            DB::commit();

            // Business Rule 7 & BR-21: Notification must be sent.
            // This is typically handled via events/listeners or queued jobs.
            // Example: event(new UserSubscriptionUpdated($user, $userSubscription));
            // For the API response, we just send a success message.

            return response()->json([
                'message' => __('MSG-23'), // Your subscription and access limit have been updated.
                'data' => [
                    'user_id' => $user->id,
                    // Return the currently active subscription
                    'current_subscription' => $user->currentActiveSubscription()->first()->load('plan')
                        ?->toArray() // Convert to array if it exists
                ],
                'warning' => ($validatedData['device_limit'] && $validatedData['device_limit'] < $currentEffectiveDeviceLimit) ? __('MSG-27') : null
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error assigning subscription for user ' . $user->id . ': ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'message' => __('MSG-28'), // An error occurred while assigning the subscription tier.
                'error' => $e->getMessage() // In production, avoid exposing internal errors
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Get a user's current subscription details.
     * Useful for pre-filling the assignment form in the frontend.
     */
    public function showCurrentSubscription(User $user): \Illuminate\Http\JsonResponse
    {
        $currentSubscription = $user->currentActiveSubscription()->first();

        if (!$currentSubscription) {
            return response()->json([
                'message' => __('MSG-20'), // No subscriptions available.
                'data' => null
            ], 200);
        }

        // Include plan details for auto-retrieval fields
        $currentSubscription->load('plan');

        return response()->json([
            'message' => 'User subscription details retrieved.',
            'data' => [
                'id' => $currentSubscription->id,
                'subscription_plan_id' => $currentSubscription->subscription_plan_id,
                'plan_name' => $currentSubscription->plan->name, // From Plan Name field description
                'subscription_type' => $currentSubscription->plan->subscription_type->value, // From Subscription Type field description
                'subscription_tier' => $currentSubscription->plan->subscription_tier->value, // From Subscription Tier field description
                'status' => $currentSubscription->status,
                'start_date' => $currentSubscription->start_date?->format('Y-m-d H:i:s'),
                'end_date' => $currentSubscription->end_date?->format('Y-m-d H:i:s'),
                // Effective limits (taking overrides into account)
                'device_limit' => $currentSubscription->device_limit,
                'user_limit' => $currentSubscription->user_limit,
                'max_dashboards' => $currentSubscription->max_dashboards,
                'custom_widgets' => $currentSubscription->custom_widgets,
                // Features (from plan, as per description)
                'features' => [
                    'data_retention' => $currentSubscription->plan->data_retention,
                    'api_access' => $currentSubscription->plan->api_access,
                    'white_labeling' => $currentSubscription->plan->white_labeling,
                    'email_alerts' => $currentSubscription->plan->email_alerts,
                    'sms_integration' => $currentSubscription->plan->sms_integration,
                    'role_management' => $currentSubscription->plan->role_management,
                    'rule_engine' => $currentSubscription->plan->rule_engine,
                ],
            ]
        ]);
    }
}
