<?php

namespace App\Http\Requests\Event;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UpdateEventRequest extends FormRequest
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
            'event_name' => 'sometimes|string|max:255|not_regex:/^[\s]*$/',
            'start_date' => 'sometimes|date|before_or_equal:end_date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'details' => 'nullable|string|max:1000',
            'reservation_id' => 'sometimes|exists:reservations,id',
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
            'event_name.string' => 'The event name must be a text.',
            'event_name.max' => 'The event name cannot exceed 255 characters.',
            'event_name.not_regex' => 'The event name cannot contain only whitespace.',

            // Start date error messages
            'start_date.date' => 'Please provide a valid date format.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',

            // End date error messages
            'end_date.date' => 'Please provide a valid date format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',

            // Details error message
            'details.string' => 'The details must be a string.',
            'details.max' => 'The details cannot exceed 1000 characters.',

            // Reservation error messages
            'reservation_id.exists' => 'The selected reservation is invalid.',
        ];
    }

    /**
     * Handle successful validation for event update request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about the event modification attempt before the
     * controller processes the actual update.
     *
     * The logging captures:
     * - Event identification and changes
     * - Schedule modifications
     * - Reservation relationship updates
     * - Modified field tracking
     * - Request metadata
     *
     * This helps monitor:
     * - Event modification patterns
     * - Scheduling changes
     * - System administration activities
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with comprehensive update details
        Log::info('Update Event form validation passed', [
            'event_id' => $this->route('event'),             // Target event identifier
            'event_name' => $this->event_name,                      // Updated event name
            'start_date' => $this->start_date,                      // Updated start time
            'end_date' => $this->end_date,                          // Updated end time
            'details' => $this->details,                            // Updated description
            'reservation_id' => $this->reservation_id,              // Updated reservation link
            'ip' => $this->ip(),                                    // Client IP for audit trail
            'user_agent' => $this->userAgent(),                     // Browser/device information
            'updated_fields' => array_keys($this->all())            // Modified field tracking
        ]);
    }

    /**
     * Handle failed validation for event update request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid modifications to existing events
     * 2. Log validation failures for monitoring
     * 3. Maintain scheduling integrity
     * 4. Ensure data consistency
     *
     * The logging helps identify:
     * - Schedule conflict issues
     * - Data validation problems
     * - Potential unauthorized modifications
     * - System usage patterns
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Update Event form validation failed', [
            'event_id' => $this->route('event'),             // Target event identifier
            'errors' => $validator->errors()->toArray(),            // Validation error details
            'ip' => $this->ip(),                                    // Client IP for security tracking
            'user_agent' => $this->userAgent()                      // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
