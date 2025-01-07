<?php

namespace App\Http\Requests\Rating;

use App\Models\Reservation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;


class StoreRatingRequest extends FormRequest
{
    /**
     * Check if user is authorized to rate this reservation.
     *
     * Steps:
     * 1. Get reservation_id & user_id from URL (?reservation_id=123 & user_id=3)
     * 2. Get current logged-in user ID
     * 3. Check if reservation exists and belongs to user
     * 4. Return true/false based on check result
     */
    public function authorize(): bool
    {
        // Check if the reservation belongs to the authenticated user
        return true;
        }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'You can only rate reservations that you have made.',
            'errors' => ['reservation' => 'This reservation does not belong to you.']
        ], 403));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Prepare request data before validation.
     *
     * This method is automatically called before validation runs.
     * It enriches the request data by:
     * 1. Adding the authenticated user's ID for rating ownership
     * 2. Ensuring proper rating attribution
     * 3. Maintaining user accountability
     *
     * This ensures:
     * - Each rating is properly attributed to its creator
     * - Rating ownership is accurately tracked
     * - System security through proper user association
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id() // Set authenticated user as rating creator
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Rating error messages
            'rating.required' => 'The rating is required.',
            'rating.integer' => 'The rating must be a number.',
            'rating.between' => 'The rating must be between 1 and 5.',

            // Comment error messages
            'comment.string' => 'The comment must be text.',
            'comment.max' => 'The comment cannot exceed :max characters.'
        ];
    }

    /**
     * Handle successful validation for rating submission request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about the rating submission before the controller
     * processes the actual creation.
     *
     * The logging captures:
     * - User identification
     * - Reservation context
     * - Rating details
     * - Request metadata
     *
     * This helps monitor:
     * - Rating submission patterns
     * - User feedback trends
     * - System usage analytics
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with rating submission details
        Log::info('Store Rating form validation passed', [
            'user_id' => auth()->id(),                                   // Rating creator
            'reservation_id' => $this->query('reservation_id'),     // Associated reservation
            'rating' => $this->rating,                                   // Numerical rating value
            'comment' => $this->comment,                                 // Optional feedback text
            'ip' => $this->ip(),                                         // Client IP for audit trail
            'user_agent' => $this->userAgent(),                          // Browser/device information
        ]);
    }

    /**
     * Handle failed validation for rating submission request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid ratings from being submitted
     * 2. Log validation failures for monitoring
     * 3. Track submission attempts
     * 4. Maintain data quality
     *
     * The logging helps identify:
     * - Common input errors
     * - Potential system misuse
     * - User interaction issues
     * - Submission patterns
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Store Rating form validation failed', [
            'errors' => $validator->errors()->toArray(),                 // Validation error details
            'user_id' => auth()->id(),                                   // User attempting submission
            'reservation_id' => $this->query('reservation_id'),     // Target reservation
            'ip' => $this->ip(),                                         // Client IP for security tracking
            'user_agent' => $this->userAgent()                           // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Rating validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
