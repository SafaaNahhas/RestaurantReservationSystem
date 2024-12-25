<?php

namespace App\Http\Requests\User;

use App\Enums\RoleUser;
use App\Models\Reservation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define validation rules for user registration.
     *
     * Rules ensure:
     * - Name is properly formatted
     * - Email is valid and unique
     * - Password meets security requirements
     * - Phone number format is valid (if provided)
     * - Account status is properly set
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|regex:/^([0-9]{10})$/',
            'is_active' => 'nullable|boolean',
            'role' => ['required', Rule::enum(RoleUser::class)]
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'E-mail',
            'password' => 'Password',
            'phone' => 'Phone Number',
            'is_active' => 'Activation Status',
        ];
    }

    public function messages(): array
    {
        return [
            // Name validation messages
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than :max characters.',

            // Email validation messages
            'email.required' => 'The email field is required.',
            'email.unique' => 'The email has already been taken.',

            // Password validation messages
            'password.required' => 'The password field is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least :min characters.',

            // Phone validation messages
            'phone.regex' => 'The phone number must be a valid phone number.',
        ];
    }

    /**
     * Handle successful validation.
     *
     * Logs registration attempts for:
     * - Security monitoring
     * - User tracking
     * - System analytics
     * - Audit compliance
     *
     * Note: Excludes sensitive data from logs
     */
    protected function passedValidation(): void
    {
        Log::info('User registration validation passed', [
            'email' => $this->email,                // User's email for identification
            'ip' => $this->ip(),                    // IP address for security tracking
            'user_agent' => $this->userAgent()      // Browser/device for analytics
        ]);
    }

    /**
     * Handle failed validation attempts.
     *
     * Provides:
     * - Secure error logging
     * - Detailed validation feedback
     * - Security monitoring data
     *
     * Security measures:
     * - Excludes passwords from logs
     * - Records attempt metadata
     * - Maintains audit trail
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::error('User registration validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation']),    // Exclude sensitive data
            'ip' => $this->ip(),                                                // IP for security monitoring
            'user_agent' => $this->userAgent()                                  // Browser/device information
        ]);

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
