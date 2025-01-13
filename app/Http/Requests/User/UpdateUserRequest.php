<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Sanitize and normalize input data before validation
     * Helps prevent XSS attacks and ensures consistent data format
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->has('name') ? strip_tags(trim($this->name)) : null,
            'email' => $this->has('email') ? strtolower(trim($this->email)) : null,
            'phone' => $this->phone ? preg_replace('/[^0-9]/', '', $this->phone) : null,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the authenticated user's ID matches the specific condition
        return auth()->check() && auth()->id() === $this->route('user')->id;
    }

    /**
     * Define validation rules for user updates.
     *
     * Rules ensure:
     * - Optional updates are properly validated
     * - Email remains unique across users
     * - Password updates meet security requirements
     * - Phone numbers maintain correct format
     * - Account status changes are valid
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',    // Optional name update
            'email' => [                             // Optional email update
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user'))  // Ignore current user's email
            ],
            'password' => 'nullable|string|min:8|confirmed',  // Optional password update
            'phone' => 'nullable|string|regex:/^([0-9]{10})$/',  // Optional phone update
            'is_active' => 'nullable|boolean',       // Optional status update
        ];
    }

    /**
     * Custom error messages.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            // Name validation messages
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than :max characters.',

            // Email validation messages
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than :max characters.',
            'email.unique' => 'The email has already been taken.',

            // Password validation messages
            'password.confirmed' => 'The email confirmation does not match.',
            'password.min' => 'The password must be at least :min characters.',
        ];
    }

    /**
     * Handle successful validation.
     *
     * Logs:
     * - User identification
     * - Modified fields tracking
     * - Update patterns
     * - Audit trail
     *
     * Security:
     * - Excludes sensitive data
     * - Tracks only modified fields
     */
    public function passedValidation(): void
    {
        Log::info('User update validation passed', [
            'user_id' => auth()->id(),                                                  // User being updated
            'modified_fields' => array_keys($this->only(['name', 'email', 'phone']))    // Changed fields
        ]);
    }

    /**
     * Handle failed validation attempts.
     *
     * Provides:
     * - Secure error logging
     * - Validation feedback
     * - Update attempt tracking
     *
     * Security measures:
     * - Excludes passwords from logs
     * - Records attempt context
     * - Maintains audit trail
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::error('User update validation failed', [
            'user_id' => auth()->id(),                      // User attempting update
            'errors' => $validator->errors()->toArray(),    // Validation errors
            'input' => $this->except([                      // Exclude sensitive data
                'password',
                'password_confirmation',
                'current_password'
            ])
        ]);

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
