<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserSubscriptionRequest extends FormRequest
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
        return [
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['active', 'pending', 'cancelled', 'expired', 'trial'])],

            'device_limit' => ['nullable', 'integer', 'min:0'],
            'user_limit' => ['nullable', 'integer', 'min:0'],
            'max_dashboards' => ['nullable', 'integer', 'min:0'],
            'custom_widgets' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subscription_plan_id.required' => __('MSG-18'),
            'subscription_plan_id.exists' => __('MSG-17'),
            'min' => 'The :attribute must be greater than or equal to zero. ' . __('MSG-17'),
            'date' => 'The :attribute must be a valid date. ' . __('MSG-17'),
            'before_or_equal' => 'The :attribute must be before or equal to the end date. ' . __('MSG-17'),
            'after_or_equal' => 'The :attribute must be after or equal to the start date. ' . __('MSG-17'),
            'in' => 'The :attribute status is invalid. ' . __('MSG-17'),
        ];
    }
}
