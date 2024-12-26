<?php
namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

/**
 * Class LoginRequest
 *
 * This class handles the validation of user login requests.
 * It ensures that the necessary data is provided and meets the specified criteria before passing the request to the controller.
 *
 * @package App\Http\Requests\AuthRequest
 */
class LoginRequest extends FormRequest
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
     * These rules ensure that the request contains a valid email and password.
     *
     * @return array The validation rules for the login request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Custom messages for email validation failures
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must be valid email',
            'email.max' => 'The email field may not be greater than :max characters.',

            // Custom messages for password validation failures
            'password.required' => 'The password field is required.',
            'password.string' => 'The password field must be of type string.',
            'password.min' => 'The password field must be at least :min characters.'
        ];
    }

    /**
     * Handle a passed validation attempt.
     * This method is called when validation passes successfully.
     * Logs successful validation attempts for security tracking.
     *
     * @return void
     */
    public function passedValidation(): void
    {
        // Log successful validation with user information for audit trail
        Log::info('Validation passed for LoginRequest', [
            'email' => $this->email,
            'ip' => $this->ip(),                    // Records the IP address for security monitoring
            'user_agent' => $this->userAgent()      // Records browser/device info for tracking
        ]);
    }

    /**
     * Handle a failed validation attempt.
     * This method is called when validation fails.
     * Logs failed attempts and throws validation exception.
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     *
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log failed validation attempts with detailed information
        Log::warning('Login validation failed', [
            'email' => $this->email ?? 'not provided',          // Log email if provided, otherwise 'not provided'
            'errors' => $validator->errors()->toArray(),        // Convert validation errors to array for logging
            'ip' => $this->ip(),                                // Log IP for security monitoring
            'user_agent' => $this->userAgent()                  // Log user agent for tracking suspicious activity
        ]);

        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
