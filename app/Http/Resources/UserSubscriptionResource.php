<?php

namespace App\Http\Resources;

use App\Http\Resources\SubscriptionPlan\SubscriptionPlanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->whenLoaded('plan');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'plan_details' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'status' => $this->status,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d H:i:s') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            'effective_limits' => [
                'device_limit' => $this->device_limit,
                'user_limit' => $this->user_limit,
                'max_dashboards' => $this->max_dashboards,
                'custom_widgets' => $this->custom_widgets,
            ],
            'features' => $this->whenLoaded('plan', function () {
                return [
                    'data_retention' => (bool)$this->plan->data_retention,
                    'api_access' => (bool)$this->plan->api_access,
                    'white_labeling' => (bool)$this->plan->white_labeling,
                    'email_alerts' => (bool)$this->plan->email_alerts,
                    'sms_integration' => (bool)$this->plan->sms_integration,
                    'role_management' => (bool)$this->plan->role_management,
                    'rule_engine' => (bool)$this->plan->rule_engine,
                ];
            }),
        ];
    }
}
