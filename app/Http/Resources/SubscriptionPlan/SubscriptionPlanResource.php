<?php

namespace App\Http\Resources\SubscriptionPlan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'device_limit' => $this->device_limit,
            'user_limit' => $this->user_limit,
            'subscription_type' => $this->subscription_type->value,
            'subscription_tier' => $this->subscription_tier->value,
            'trial_period_days' => $this->trial_period_days,
            'is_active' => $this->is_active,
            'max_dashboards' => $this->max_dashboards,
            'data_retention' => $this->data_retention,
            'api_access' => $this->api_access,
            'white_labeling' => $this->white_labeling,
            'custom_widgets' => $this->custom_widgets,
            'email_alerts' => $this->email_alerts,
            'sms_integration' => $this->sms_integration,
            'role_management' => $this->role_management,
            'rule_engine' => $this->rule_engine,
            'is_default' => $this->is_default,
            'assigned_users_count' => $this->whenLoaded('userSubscriptions', function() {
                return $this->user_subscriptions_count ?? 0;
            }, 0),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->whenNotNull($this->deleted_at?->format('Y-m-d H:i:s')),
        ];
    }
}
