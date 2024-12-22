<?php

namespace App\Http\Requests\FoodCategoryRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class StoreFoodCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
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
            'category_name' => 'required|string|max:50|not_regex:/^[\s]*$/',
            'description' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id|integer',
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
            // Food Category name error messages
            'category_name.required' => 'The category name is required.',
            'category_name.string' => 'The category name must be string.',
            'category_name.max' => 'The category name cannot exceed 50 characters.',
            'category_name.unique' => 'This category name already exists.',
            'category_name.not_regex' => 'The category name cannot contain only whitespace.',

            // Description error messages
            'description.string' => 'The description must be string.',
            'description.max' => 'The description cannot exceed :max characters.',

            // User error messages
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'user_id.integer' => 'The user ID must be a number.',
        ];
    }

    /**
     * Prepare request data before validation.
     *
     * This method is automatically called before validation runs.
     * It enriches the request data by:
     * 1. Adding the creator's user ID from authentication
     * 2. Ensuring category creation tracking
     * 3. Maintaining creator accountability
     */
    protected function prepareForValidation(): void
    {
        // Merge authenticated user's ID as category creator
        $this->merge([
            'user_id' => auth()->id(),                    // Set current user as creator
        ]);
    }

    /**
     * Handle successful validation for food category creation request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about the category creation attempt before the
     * controller processes the request.
     *
     * The logging captures:
     * - Category details
     * - Creator information
     * - Request metadata
     *
     * This helps monitor:
     * - Menu organization patterns
     * - Category creation history
     * - Creator accountability
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with category creation details
        Log::info('Store Food Category form validation passed', [
            'category_name' => $this->category_name,       // New category identifier
            'description' => $this->description,           // Category description
            'user_id' => $this->user_id,                   // Category creator ID
            'ip' => $this->ip(),                           // Client IP for audit trail
            'user_agent' => $this->userAgent(),            // Browser/device information
        ]);
    }

    /**
     * Handle failed validation for food category creation request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid categories from being created
     * 2. Log validation failures for quality control
     * 3. Track failed creation attempts
     * 4. Provide consistent error feedback
     *
     * The logging helps identify:
     * - Common input errors
     * - Duplicate category attempts
     * - Creation permission issues
     * - User interaction problems
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Store Food Category form validation failed', [
            'errors' => $validator->errors()->toArray(),    // Validation error details
            'ip' => $this->ip(),                            // Client IP for security tracking
            'user_agent' => $this->userAgent()              // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
