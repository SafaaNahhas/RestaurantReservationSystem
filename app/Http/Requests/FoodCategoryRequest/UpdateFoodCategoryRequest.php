<?php

namespace App\Http\Requests\FoodCategoryRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateFoodCategoryRequest extends FormRequest
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
            'category_name' => 'sometimes|string|max:50|unique:food_categories|not_regex:/^[\s]*$/',
            'description' => 'nullable|string|max:255',
            'user_id' => 'sometimes|exists:users,id|integer',
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
            'category_name.string' => 'The category name must be string.',
            'category_name.max' => 'The category name cannot exceed :max characters.',
            'category_name.unique' => 'This category name already exists.',
            'category_name.not_regex' => 'The category name cannot contain only whitespace.',

            // Description error messages
            'description.string' => 'The description must be string.',
            'description.max' => 'The description cannot exceed :max characters.',

            // User error messages
            'user_id.exists' => 'The selected user does not exist.',
            'user_id.integer' => 'The user ID must be a number.',
        ];
    }

    /**
     * Handle successful validation for food category update request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about who is making content changes while preserving
     * the original creator in the database.
     *
     * The logging captures:
     * - Category identification
     * - Content modifications
     * - Changed fields tracking
     * - Who made the changes
     * - Request metadata
     *
     * This helps monitor:
     * - Which fields are being modified
     * - Who is updating category content
     * - What changes are being made
     * - Audit trail of modifications
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Track which fields are being updated
        $updatedFields = array_keys($this->only([
            'category_name',
            'description'
        ]));

        // Log successful validation with update details and who made the changes
        Log::info('Update Food Category form validation passed', [
            'category_id' => $this->route('category'),       // Target category identifier
            'category_name' => $this->category_name,                // Updated category name
            'description' => $this->description,                    // Updated description
            'modified_by' => auth()->id(),                          // Who made this update
            'updated_fields' => $updatedFields,                     // List of fields being modified
            'ip' => $this->ip(),                                    // Client IP for audit trail
            'user_agent' => $this->userAgent(),                     // Browser/device information
        ]);
    }

    /**
     * Handle failed validation for food category update request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid modifications to existing categories
     * 2. Log validation failures for monitoring
     * 3. Track failed update attempts
     * 4. Maintain data integrity
     *
     * The logging helps identify:
     * - Update validation issues
     * - Common modification errors
     * - Potential unauthorized changes
     * - User interaction problems
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Update Food Category form validation failed', [
            'category_id' => $this->route('category'),       // Target category identifier
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
