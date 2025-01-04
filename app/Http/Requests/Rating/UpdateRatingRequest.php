<?php

namespace App\Http\Requests\Rating;

use App\Models\Rating;
use App\Models\Reservation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class UpdateRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to update this rating.
     *
     * Authorization rules:
     * 1. Rating must exist and belong to the specified reservation
     * 2. The reservation must belong to the authenticated user
     *
     * This ensures:
     * - Users can only modify their own ratings
     * - Ratings stay associated with correct reservations
     * - System maintains data integrity
     * - Unauthorized modifications are prevented
     * - Data consistency is maintained across relationships
     *
     * @throws HttpResponseException When authorization fails
     * @return bool Returns true if user is authorized, false otherwise
     */
    public function authorize(): bool
    {
        // Get rating instance from route parameter
        $rating = $this->route('rating');
        if (!($rating instanceof \App\Models\Rating)) {
            $rating = Rating::find($rating);
        }

        // If rating doesn't exist, deny access
        if (!$rating) {
            return false;
        }

        // Get the reservation associated with this rating
        $reservation = $rating->reservation;

        // If no reservation found, deny access
        if (!$reservation) {
            return false;
        }

        // Verify reservation belongs to authenticated user
        return $reservation->user_id === auth()->id();
    }

    /**
     * Handle a failed authorization attempt.
     * This method is triggered when the authorize() method returns false.
     *
     * @throws HttpResponseException With a 403 status code and JSON error details
     * @return void
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'You can only update your own ratings.',
            'errors' => ['rating' => 'This rating does not belong to you.']
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
            'rating' => 'sometimes|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
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
     * Handle successful validation for rating update request.
     *
     * This method logs:
     * - Rating identification
     * - User making changes
     * - Updated content
     * - Request metadata
     *
     * This helps track:
     * - Rating modification history
     * - User update patterns
     * - Content changes
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Update Rating form validation passed', [
            'rating_id' => $this->route('rating'),        // Rating being updated
            'user_id' => auth()->id(),                           // User making the update
            'rating' => $this->rating,                           // New rating value
            'comment' => $this->comment,                         // New comment content
            'ip' => $this->ip(),                                 // Client IP for audit trail
            'user_agent' => $this->userAgent(),                  // Browser/device information
        ]);
    }

    /**
     * Handle failed validation for rating update request.
     *
     * This method captures:
     * - Validation errors
     * - Failed update attempts
     * - User information
     * - Request context
     *
     * This helps identify:
     * - Common update issues
     * - Invalid modification attempts
     * - Potential system misuse
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Update Rating form validation failed', [
            'errors' => $validator->errors()->toArray(),            // Validation error details
            'rating_id' => $this->route('rating'),           // Rating attempted to update
            'user_id' => auth()->id(),                              // User attempting update
            'ip' => $this->ip(),                                    // Client IP for security
            'user_agent' => $this->userAgent()                      // Browser/device information
        ]);

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Rating update validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
