<?php

namespace App\Http\Requests\Emergency;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

/**
 * Class UpdateEmergencyRequest
 *
 * Handles the validation of requests to update emergencies in the system.
 */
class UpdateEmergencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the user is authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'nullable|string|min:3|max:200',
            'start_date'  => 'nullable|date|after_or_equal:today',
            'end_date'    => 'nullable|date|after:start_date',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean'
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string> The human-readable names for the request attributes.
     */
    public function attributes(): array
    {
        return [
            'name'        => 'Emergency situation',
            'start_date'  => 'Emergency start date',
            'end_date'    => 'Emergency end date',
            'description' => 'Emergency situation details',
            'is_active'      => 'Emergency status'
        ];
    }

    /**
     * Get custom validation messages for errors.
     *
     * @return array<string, string> Custom error messages for the request validation.
     */
    public function messages(): array
    {
        return [
            // Name validation messages
            'name.string' => 'The :attribute name must be string.',
            'name.min' => 'The :attribute name must be at least :min characters.',
            'name.max' => 'The :attribute name cannot exceed :max characters.',

            // Start Date validation messages
            'start_date.date' => 'The :attribute must be a valid date.',
            'start_date.after_or_equal' => 'The :attribute cannot be in the past.',

            // End Date validation messages
            'end_date.date' => 'The :attribute must be a valid date',
            'end_date.after' => 'The :attribute must be after the start date',

            // Description validation messages
            'description.string' => 'The :attribute must be text',

            // is_active validation messages
            'is_active.boolean'  => 'The :attribute must be boolean value'
        ];
    }

    /**
     * Handle logic after validation passes.
     *
     * Logs a message indicating that the validation passed.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Update Emergency form validation passed', [
            'name'        => $this->name,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'description' => !empty($this->description) ? $this->description : null,
            'is_active'   => $this->is_active
        ]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * Logs a warning with details of the validation errors and throws an HTTP response exception.
     *
     * @param Validator $validator The validator instance containing the validation errors.
     *
     * @throws HttpResponseException A JSON response with validation errors and a 422 status code.
     *
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Update Emergency form validation failed', [
            'errors'      => $validator->errors()->toArray(), // Validation issues
            'ip'          => $this->ip(),                    // Client IP for security
            'user_agent'  => $this->userAgent(),             // Browser/device info
        ]);

        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
