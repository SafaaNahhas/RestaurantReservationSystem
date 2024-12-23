<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class AddToFavoritesRequest extends FormRequest
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
    public function rules()
    {
        return [
            'type' => 'required|in:tables,food_categories', 
            'id' => 'required|integer',          
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
            'type' => 'type',
            'id' => 'id',
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute is required',
            'type.in' => 'The type must be tables or food_categories',
        ];
    }
}
