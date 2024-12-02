<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
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
            'email' => ['sometimes', 'email', 'max:255',
                   Rule::unique('users')->ignore(auth()->id())],
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'nullable|string|regex:/^([0-9]{10})$/',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'E-mail',
            'password' => 'Password',
            'phone' => 'Phone Number',
            'is_active' => 'Activation Status',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'name.string' => 'The :attribute must be a string.' ,
            'name.max' => 'The :attribute may not be greater than :max characters.' ,
            'email.email' => 'The :attribute must be a valid email address.' ,
            'email.max' => 'The :attribute may not be greater than :max characters.',
            'email.unique' => 'The :attribute has already been taken.',
            'password.confirmed' => 'The :attribute confirmation does not match.',
        ];
    }

    public function passedValidation(): void
    {
        Log::info('Validation passed for UpdateUserRequest');
    }

    /**
     * Handle failed validation
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        Log::error('Validation failed for UpdateUserRequest', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

}
