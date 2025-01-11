<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

/**
 * Class RegisterRequest
 *
 * This class handles the validation of user registration requests.
 * It ensures that the necessary data is provided and meets the specified criteria before passing the request to the controller.
 *
 * @package App\Http\Requests\AuthRequest
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always returns true, allowing everyone to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow everyone to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * These rules ensure that the request contains a valid name, email, and password.
     *
     * @return array The validation rules for the registration request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'min:8',
                'string',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            // Custom messages for name validation failures
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than :max characters.',

            // Custom messages for email validation failures
            'email.required' => 'The email field is required.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than :max characters.',
            'email.unique' => 'The email has already been taken.',

            // Custom messages for password validation failures
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least :min characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password must contain an uppercase letter, a lowercase letter, a number and one of the following symbols: @$!%*#?&'
        ];
    }

    /**
     * Handles successful validation.
     * Logs successful validation attempts for audit purposes.
     * Includes email, IP address, and user agent for security tracking.
     * Sanitizes and prepares the data for storage.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with security context
        Log::info('Validation passed for RegisterRequest', [
            'email' => $this->email,
            'name' => $this->name,
            'ip' => $this->ip(),                    // Records the IP address for security monitoring
            'user_agent' => $this->userAgent(),     // Records browser/device info for tracking
        ]);
    }

    /**
     * Handles failed validation attempts.
     * Logs validation failures with relevant details for debugging and monitoring.
     * Excludes sensitive data (password) from logs.
     * Uses parent's failedValidation to maintain consistent error response format.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with relevant details
        Log::error('Validation failed for RegisterRequest', [
            'errors' => $validator->errors()->toArray(),                        // Detailed validation errors for debugging
            'input' => $this->except(['password', 'password_confirmation']),    // Request data excluding sensitive fields
            'ip' => $this->ip(),                                                // Client IP for security tracking
            'user_agent' => $this->userAgent(),                                 // Browser/device info for user analytics
        ]);

        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}