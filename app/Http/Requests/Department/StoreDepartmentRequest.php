<?php

namespace App\Http\Requests\Department;

use App\Rules\ImageNumeCheck;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
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
        * رسائل التحقق المخصصة.
        *
        * @return array
        */
    public function messages()
    {
        return [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name must not exceed 255 characters.',
            'name.unique' => 'The category name already exists.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 1000 characters.',

        ];
    }
}
