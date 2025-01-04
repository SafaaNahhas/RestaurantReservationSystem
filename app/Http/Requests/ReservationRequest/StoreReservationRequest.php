<?php

namespace App\Http\Requests\ReservationRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreReservationRequest extends FormRequest
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
            'table_number' => 'nullable|exists:tables,table_number',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',

            'guest_count' => 'required|integer|min:1',
            'services' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Start date validation messages
            'start_date.required' => 'Please select when you would like to start your reservation.',
            'start_date.date' => 'Please provide a valid date and time.',
            'start_date.after_or_equal' => 'Reservations must be for today or a future date.',

            // End date validation messages
            'end_date.date' => 'Please provide a valid end time.',
            'end_date.after' => 'The end time must be after the start time.',

            // Table Number validation messages
            'table_number.exists' => 'The selected table does not exist.',

            // Guest count validation messages
            'guest_count.required' => 'Please specify the number of guests.',
            'guest_count.integer' => 'The number of guests must be a whole number.',
            'guest_count.min' => 'The number of guests must be at least 1.',
        ];
    }

    /**
     * Prepare request data before validation.
     *
     * This method automatically:
     * 1. Sets the authenticated user as reservation owner
     * 2. Initializes reservation with 'pending' status
     * 3. Ensures proper attribution and state
     *
     * This helps:
     * - Track reservation ownership
     * - Maintain consistent initial states
     * - Streamline reservation processing
     * - Ensure data completeness
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),     // Set authenticated user as owner
            'status' => 'pending',         // Initialize as pending reservation
        ]);
    }

    /**
     * Handle successful validation for reservation request.
     *
     * This method logs comprehensive reservation details including:
     * - Customer information
     * - Reservation specifics
     * - Timing details
     * - Service requests
     * - Request metadata
     *
     * This helps monitor:
     * - Reservation patterns
     * - Resource utilization
     * - Customer preferences
     * - System usage
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Store Reservation form validation passed', [
            'user_id' => $this->user_id,          // Customer identification
            'table_id' => $this->table_id,        // Reserved table
            'start_date' => $this->start_date,    // Reservation start time
            'end_date' => $this->end_date,        // Reservation end time
            'guest_count' => $this->guest_count,  // Party size
            'services' => $this->services,        // Additional services
            'status' => $this->status,            // Reservation status
            'ip' => $this->ip(),                  // Client IP for audit
            'user_agent' => $this->userAgent(),   // Browser/device info
        ]);
    }

    /**
     * Handle failed validation for reservation request.
     *
     * This method captures:
     * - Validation errors
     * - Request metadata
     *
     * Keeps logging focused on:
     * - Error tracking
     * - Security monitoring
     * - System auditing
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Store Reservation form validation failed', [
            'errors' => $validator->errors()->toArray(), // Validation issues
            'ip' => $this->ip(),                         // Client IP for security
            'user_agent' => $this->userAgent()           // Browser/device info
        ]);

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
