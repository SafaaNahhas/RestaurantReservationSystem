<?php

namespace App\Http\Requests\Department;

use App\Rules\ImageNumeCheck;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;

class StoreDepartmentRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
            'manager_id' => 'required|exists:users,id',
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
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed :max characters.',
            'name.unique' => 'The name already exists.',

            // Description validation messages
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed :max characters.',

            // Manager validation messages
            'manager_id.required' => 'The manager is required.',
            'manager_id.exists' => 'The selected manager does not exist.',

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
     * Handle successful validation for department creation.
     *
     * This method is automatically called by Laravel form request
     * when all validation rules pass successfully. It provides
     * an opportunity to perform additional operations or logging
     * before the main controller action is executed.
     *
     * The method logs successful validation attempts to help monitor
     * system usage and track form submission patterns.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        // Log successful validation with department and image details
        Log::info('Store Department form validation passed', [
            'department_name' => $this->name,                           // Department being created
            'description' => $this->description,                        // Description of the department
            'manager_id' => $this->manager_id,                          // Manager assigned to the department
            'ip' => $this->ip(),                                        // Client IP for request tracking
            'user_agent' => $this->userAgent(),                         // Browser/device information
            'has_images' => $this->hasFile('images'),              // Whether images were uploaded
            'image_count' => $this->hasFile('images') ?            // Number of images if any
                count($this->file('images')) : 0
        ]);
    }

    /**
     * Handle failed validation for department creation.
     *
     * This method is automatically triggered when validation rules fail.
     * It interrupts the normal request lifecycle and returns an error
     * response to the client instead of proceeding to the controller.
     *
     * The method serves three main purposes:
     * 1. Logs validation failures for monitoring and debugging
     * 2. Maintains consistent error response format across the application
     * 3. Prevents invalid data from reaching the controller
     *
     * @param Validator $validator The validator instance containing error details
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failures with relevant debugging information
        Log::warning('Department form validation failed', [
            'errors' => $validator->errors()->toArray(),        // Detailed validation errors
            'input' => $this->except(['images']),              // Form input excluding file data
            'ip' => $this->ip(),                               // Client IP for security tracking
            'user_agent' => $this->userAgent()                 // Browser/device information
        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
