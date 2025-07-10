<?php

namespace App\Http\Requests\SubscriptionPlan;

use App\Enums\SubscriptionTier;
use App\Enums\SubscriptionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $planId = $this->route('subscription_plan');

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('subscription_plans')->ignore($planId)],
            'description' => ['nullable', 'string', 'max:250'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'device_limit' => ['required', 'integer', 'min:1'],
            'user_limit' => ['required', 'integer', 'min:1'],
            'subscription_type' => ['required', Rule::enum(SubscriptionType::class)],
            'subscription_tier' => ['required', Rule::enum(SubscriptionTier::class)],
            'trial_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'max_dashboards' => ['required', 'integer', 'min:1'],
            'data_retention' => ['nullable', 'boolean'],
            'api_access' => ['nullable', 'boolean'],
            'white_labeling' => ['nullable', 'boolean'],
            'custom_widgets' => ['required', 'integer', 'min:1'],
            'email_alerts' => ['nullable', 'boolean'],
            'sms_integration' => ['nullable', 'boolean'],
            'role_management' => ['nullable', 'boolean'],
            'rule_engine' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'is_trial' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return (new StoreSubscriptionPlanRequest())->messages();
    }
}
