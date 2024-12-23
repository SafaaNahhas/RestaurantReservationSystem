<?php

namespace App\Http\Requests\Rating;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateRatingRequest extends FormRequest
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
            'rating' => 'nullable |integer|in:1,2,3,4,5',
            'comment' => 'nullable |string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'rating' => 'error',
            'message' => 'please make sure for the inputs  ',
            'errors' => $validator->errors(),

        ],422));
    }

    public function attributes()
    {
        return [
            'rating' => 'rating',
            'comment' => 'comment',
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute is required',
            'rating.in' => 'The rating must be one of the following values:1,2,3,4,5',
        ];
    }
}
