<?php

namespace App\Http\Requests\DishRequest;

use App\Rules\ImageNumeCheck;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreDishRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:food_categories,id',
           'images' => [
                'sometimes',
                'array', // Make sure 'images' is an array
                'max:5', // Optional: limit the maximum number of images
            ],
            'images.*' => [ // Validate each image in the array
                'image', // The file must be an image
                'mimes:jpeg,png,gif,webp', // Must be of type jpeg, png, gif, or webp
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
            'name.required' => 'The dish name is required.',
            'name.string' => 'The dish name must be a string.',
            'name.max' => 'The dish name must not exceed :max characters.',

            // Description validation messages
            'description.string' => 'The description must be a string.',

            // Category validation messages
            'category_id.required' => 'The food category is required.',
            'category_id.exists' => 'The selected food category does not exist.',

            // Images validation messages
            'images.array' => 'Images must be provided in the correct format.',
            'images.max' => 'You cannot upload more than 5 images.',

            // Individual image validation messages
            'images.*.image' => 'Each uploaded file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, GIF, and WEBP images are allowed.',
        ];
    }

    /**
     * Handle successful validation for dish creation request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs the dish
     * creation attempt before the controller processes the request.
     *
     * The logging captures:
     * - Dish details and categorization
     * - Image upload information
     * - Request metadata
     *
     * This helps monitor:
     * - Menu expansion patterns
     * - Content creation workflow
     * - System usage and administration activities
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with dish creation details
        Log::info('Store Dish form validation passed', [
            'dish_name' => $this->name,                           // New dish identifier
            'category_id' => $this->category_id,                  // Menu categorization
            'ip' => $this->ip(),                                  // Client IP for audit trail
            'user_agent' => $this->userAgent(),                   // Browser/device information
            'has_images' => $this->hasFile('images'),        // Image upload status
            'image_count' => $this->hasFile('images') ?      // Number of dish images
                count($this->file('images')) : 0
        ]);
    }

    /**
     * Handle failed validation for dish creation request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid dish entries from being created
     * 2. Log validation failures for quality control
     * 3. Provide consistent error feedback
     *
     * The logging helps identify:
     * - Data entry issues
     * - Common validation problems
     * - Potential system misuse
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Store Dish form validation failed', [
            'errors' => $validator->errors()->toArray(),      // Validation error details
            'input' => $this->except(['images']),            // Form data excluding images
            'ip' => $this->ip(),                             // Client IP for security tracking
            'user_agent' => $this->userAgent()               // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
