<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreEventRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255|not_regex:/^[\s]*$/',
            'start_date' => 'required|date|before_or_equal:end_date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'details' => 'nullable|string|max:1000',
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
            // Event name error messages
            'event_name.required' => 'The event name is required.',
            'event_name.max' => 'The event name cannot exceed :max characters.',
            'event_name.not_regex' => 'The event name cannot contain only whitespace.',

            // Start date error messages
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please provide a valid date format.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',

            // End date error messages
            'end_date.date' => 'Please provide a valid date format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',

            // Details error message
            'details.string' => 'The details must be a string.',
            'details.max' => 'The details cannot exceed :max characters.',

            // Reservation error messages
            'reservation_id.required' => 'The reservation is required.',
            'reservation_id.exists' => 'The selected reservation is invalid.',
        ];
    }

    /**
     * Handle successful validation for event creation request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs comprehensive
     * information about the event creation attempt before the controller
     * processes the request.
     *
     * The logging captures:
     * - Event details and scheduling
     * - Reservation associations
     * - Request metadata
     *
     * This helps monitor:
     * - Event scheduling patterns
     * - Reservation-event relationships
     * - System usage analytics
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with event creation details
        Log::info('Store Event form validation passed', [
            'event_name' => $this->event_name,                // Event identifier
            'start_date' => $this->start_date,                // Event start timing
            'end_date' => $this->end_date,                    // Event end timing
            'details' => $this->details,                      // Event description
            'reservation_id' => $this->reservation_id,        // Linked reservation
            'ip' => $this->ip(),                              // Client IP for audit trail
            'user_agent' => $this->userAgent(),               // Browser/device information
        ]);
    }

    /**
     * Handle failed validation for event creation request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid events from being created
     * 2. Log validation failures for monitoring
     * 3. Ensure event scheduling integrity
     * 4. Provide consistent error feedback
     *
     * The logging helps identify:
     * - Scheduling conflicts
     * - Data entry issues
     * - Potential system misuse
     * - Common validation problems
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Store Event form validation failed', [
            'errors' => $validator->errors()->toArray(),      // Validation error details
            'ip' => $this->ip(),                              // Client IP for security tracking
            'user_agent' => $this->userAgent()                // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
