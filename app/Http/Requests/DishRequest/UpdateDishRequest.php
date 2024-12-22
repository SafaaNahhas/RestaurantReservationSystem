<?php

namespace App\Http\Requests\DishRequest;

use App\Rules\ImageNumeCheck;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UpdateDishRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:food_categories,id',
            'images' => [
                'sometimes',
                'array', // Make sure 'images' is an array
                'max:5', // Optional: limit the maximum number of images
            ],
            'images.*' => [ // Validate each image in the array
                'required_with:images', // Each file is required when 'images' exists
                'image', // Must be an image
                'mimes:jpeg,png,gif,webp', // Limit allowed formats
                'max:2048', // Limit individual image size to 2MB
                new ImageNumeCheck(), // Using a custom rule to check the file name
            ],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // Name validation messages
            'name.string' => 'The dish name must be a string.',
            'name.max' => 'The dish name must not exceed :max characters.',

            // Description validation messages
            'description.string' => 'The description must be a string.',

            // Category validation messages
            'category_id.exists' => 'The selected food category does not exist.',

            // Images validation messages
            'images.array' => 'Images must be provided in the correct format.',
            'images.max' => 'You cannot upload more than 5 images.',
            'images.*.max' => 'Each image must not exceed 2MB in size.',

            // Individual image validation messages
            'images.*.image' => 'Each uploaded file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, GIF, and WEBP images are allowed.',
        ];
    }

    /**
     * Handle successful validation for dish update request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about the dish modification attempt before the
     * controller processes the actual update.
     *
     * The logging captures:
     * - Dish identification and changes
     * - Category modifications
     * - Image updates
     * - Modified field tracking
     * - Request metadata
     *
     * This helps monitor:
     * - Menu modification patterns
     * - Content update history
     * - System administration activities
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with comprehensive update details
        Log::info('Update Dish form validation passed', [
            'dish_id' => $this->route('dish'),                // Target dish identifier
            'dish_name' => $this->name,                              // Updated dish name
            'description' => $this->description,                     // Updated description
            'category_id' => $this->category_id,                     // Updated category
            'ip' => $this->ip(),                                     // Client IP for audit trail
            'user_agent' => $this->userAgent(),                      // Browser/device information
            'has_images' => $this->hasFile('images'),           // Image update status

            'image_count' => $this->hasFile('images') ?         // Number of new images
                count($this->file('images')) : 0,

            'updated_fields' => array_keys($this->only([             // Fields being modified
                'name', 'description', 'category_id', 'images'
            ]))
        ]);
    }

    /**
     * Handle failed validation for dish update request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid modifications to existing dishes
     * 2. Log validation failures for quality control
     * 3. Maintain data integrity in the menu system
     * 4. Provide consistent error responses
     *
     * The logging helps identify:
     * - Update attempt issues
     * - Data validation problems
     * - Potential unauthorized modifications
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Update Dish form validation failed', [
            'dish_id' => $this->route('dish'),               // Target dish identifier
            'errors' => $validator->errors()->toArray(),            // Validation error details
            'input' => $this->except(['images']),                   // Form data excluding images
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
