<?php

namespace App\Http\Requests\Department;

use App\Rules\ImageNumeCheck;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class UpdateDepartmentRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255|unique:departments,name,' . $this->route('department')->id,
            'description' => 'sometimes|string|max:1000',
            'manager_id' => 'sometimes|exists:users,id',
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
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than :max characters.',
            'name.unique' => 'The name already exists.',

            // Description validation messages
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than :max characters',

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
     * Handle successful validation for department update request.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It logs detailed
     * information about the update attempt before the controller
     * processes the actual update.
     *
     * The logging captures:
     * - Department identification
     * - Changed content details
     * - Image upload information
     * - Request metadata for audit trails
     *
     * This helps track and monitor:
     * - Department modification patterns
     * - Content change history
     * - System usage analytics
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Department update validation passed', [
            'department_id' => $this->route('department'),       // Target department identifier
            'name' => $this->name,                                      // Updated department name
            'description' => $this->description,                        // Updated description
            'ip' => $this->ip(),                                        // Client IP for audit trail
            'user_agent' => $this->userAgent(),                         // Browser/device information
            'has_new_images' => $this->hasFile('images'),          // Image upload status

            'image_count' => $this->hasFile('images') ?            // Number of new images
                count($this->file('images')) : 0,

            'updated_fields' => array_keys($this->only(                 // Fields being modified
                ['name', 'description', 'images']))
        ]);
    }

    /**
     * Handle failed validation for department update request.
     *
     * This method is automatically triggered when validation rules fail.
     * It serves as a checkpoint to:
     * 1. Prevent invalid updates from being processed
     * 2. Log validation failures for monitoring
     * 3. Provide consistent error responses
     *
     * The logging helps identify:
     * - Common validation issues
     * - Potential system misuse
     * - User interaction problems
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('Update Department form validation failed', [
            'department_id' => $this->route('department'),     // Target department identifier
            'errors' => $validator->errors()->toArray(),              // Validation error details
            'ip' => $this->ip(),                                      // Client IP for security tracking
            'user_agent' => $this->userAgent()                        // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
