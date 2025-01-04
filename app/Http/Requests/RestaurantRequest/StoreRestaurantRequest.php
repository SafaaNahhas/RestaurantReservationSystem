<?php

namespace App\Http\Requests\RestaurantRequest;

use App\Rules\ImageNumeCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreRestaurantRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'opening_hours' => 'required|date_format:g:i A',
            'closing_hours' => 'required|date_format:g:i A|after:opening_hours',
            'rating' => 'required|numeric|between:1,5',
            'website' => 'required|url',
            'PhoneNumbers' => 'nullable|array',
            'PhoneNumbers.*.PhoneNumber' => 'nullable|string|digits_between:1,15|unique:phone_numbers,PhoneNumber',
            'PhoneNumbers.*.description' => 'nullable|string',
            'emails' => 'nullable|array',
            'emails.*.email' => 'required|email|unique:emails,email',
            'emails.*.description' => 'nullable|string',
            'images.*' => [
                'image',
                'mimes:jpeg,png,gif,webp',
                new ImageNumeCheck(),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
