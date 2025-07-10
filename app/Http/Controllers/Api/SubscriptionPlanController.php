<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionTier;
use App\Enums\SubscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPlan\StoreSubscriptionPlanRequest;
use App\Http\Requests\SubscriptionPlan\UpdateSubscriptionPlanRequest;
use App\Http\Resources\SubscriptionPlan\SubscriptionPlanResource;
use App\Http\Resources\SubscriptionPlan\SubscriptionPlanShowResource;
use App\Models\SubscriptionPlan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SubscriptionPlan::query();

        $query->withCount('userSubscriptions');

        // Basic search by name (Acceptance Criteria)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool)$request->input('status'));
        }

        if ($request->filled('subscription_type')) {
            $type = $request->input('subscription_type');
            if (in_array($type, array_column(SubscriptionType::cases(), 'value'))) {
                $query->where('subscription_type', $type);
            } else {
                throw ValidationException::withMessages([
                    'subscription_type' => __('MSG-18'),
                ]);
            }
        }

        if ($request->filled('subscription_tier')) {
            $tier = $request->input('subscription_tier');
            if (in_array($tier, array_column(SubscriptionTier::cases(), 'value'))) {
                $query->where('subscription_tier', $tier);
            } else {
                throw ValidationException::withMessages([
                    'subscription_tier' => __('MSG-18'),
                ]);
            }
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $sortableColumns = [
            'name', 'price', 'subscription_type', 'device_limit',
            'created_at', 'user_subscriptions_count'
        ];

        if (in_array($sortBy, $sortableColumns)) {
            if ($sortBy === 'user_subscriptions_count') {
                $query->orderByRaw('user_subscriptions_count ' . ($sortOrder === 'asc' ? 'ASC' : 'DESC'));
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            throw ValidationException::withMessages([
                'sort_by' => 'Invalid sort column. ' . __('messages.MSG-18'),
            ]);
        }

        $plans = $query->paginate(10);

        if ($plans->isEmpty()) {
            return $this->successResponse(null, __('messages.MSG-21'));
        }

        return $this->successResponse([
            'data' => SubscriptionPlanResource::collection($plans),
            'pagination' => [
                'current_page' => $plans->currentPage(),
                'total_pages' => $plans->lastPage(),
                'total_records' => $plans->total(),
                'per_page' => $plans->perPage(),
            ]
        ], 'Subscription plans retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            if ($request->boolean('is_default')) {
                SubscriptionPlan::where('is_default', true)->update(['is_default' => false]);
            }

            $plan = SubscriptionPlan::create($request->validated());

            DB::commit();
            return $this->successResponse(new SubscriptionPlanResource($plan), __('messages.MSG-17'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubscriptionPlanController@store' . $e->getMessage());
            return $this->errorResponse(__('messages.MSG-18'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        $subscriptionPlan->loadCount('userSubscriptions');

        return $this->successResponse(new SubscriptionPlanShowResource($subscriptionPlan), 'Subscription plan details retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        DB::beginTransaction();
        try {
            if ($request->boolean('is_default')) {
                SubscriptionPlan::where('is_default', true)
                    ->where('id', '!=', $subscriptionPlan->id)
                    ->update(['is_default' => false]);
            }

            $subscriptionPlan->update($request->validated());

            DB::commit();
            return $this->successResponse(new SubscriptionPlanResource($subscriptionPlan), 'Subscription plan updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubscriptionPlanController@update' . $e->getMessage());
            return $this->errorResponse(__('messages.MSG-18'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        if ($subscriptionPlan->is_default) {
            return $this->errorResponse('The default plan cannot be deleted.', 403);
        }

        if ($subscriptionPlan->isInUse()) {
            return $this->errorResponse(__('messages.MSG-22'), 403);
        }

        $subscriptionPlan->delete();

        return $this->successResponse(null, 'Subscription plan deleted successfully.');
    }

    public function toggleStatus(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        DB::beginTransaction();
        try {
            $subscriptionPlan->is_active = !$subscriptionPlan->is_active;
            $subscriptionPlan->save();

            DB::commit();

            return $this->successResponse(new SubscriptionPlanResource($subscriptionPlan->fresh()), 'Subscription plan updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubscriptionPlanController@toggleStatus' . $subscriptionPlan->id . ': ' . $e->getMessage());
            return $this->errorResponse(__('messages.MSG-18'));
        }
    }
}
