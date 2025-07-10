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
            'status' => ['nullable', Rule::in(['active', 'pending', 'cancelled', 'expired', 'trial'])],
            'user_name' => ['required','string'],
            'user_email' =>['required','email']
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
            'in' => 'The :attribute status is invalid. ' . __('MSG-17'),
        ];
    }
}
