<?php

namespace App\Http\Requests\DishRequest;

use App\Rules\ImageNumeCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:food_categories,id',
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
    * Handle a failed validation attempt.
    *
    * This method is called when validation fails. It customizes the
    * response that is returned when validation fails, including the
    * status code and error messages.
    *
    * @param \Illuminate\Contracts\Validation\Validator $validator
    * @throws \Illuminate\Http\Exceptions\HttpResponseException
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
