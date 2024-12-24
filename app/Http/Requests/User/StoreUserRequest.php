<?php

namespace App\Http\Requests\User;

use App\Enums\RoleUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|regex:/^([0-9]{10})$/',
            'is_active' => 'nullable|boolean',
            'role' => [Rule::enum(RoleUser::class)]
        ];
    }

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

    public function messages(): array
    {
        return [
            'name.required' => 'The :attribute field is required.',
            'name.string' => 'The :attribute must be a string.',
            'name.max' => 'The :attribute may not be greater than :max characters.',
            'email.required' => 'The :attribute field is required.',
            'email.unique' => 'The :attribute has already been taken.',
            'password.required' => 'The :attribute field is required.',
            'password.confirmed' => 'The :attribute confirmation does not match.',
            'password.min' => 'The :attribute must be at least :min characters.',
            'phone.regex' => 'The :attribute must be a valid phone number.',
        ];
    }

    public function passedValidation(): void
    {
        Log::info('Validation passed for StoreUserRequest');
    }

    /**
     * Handle failed validation
     *
     * @param Validator $validator
     * @throws HttpResponseException
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        //Logs validation errors but excludes sensitive password data
        Log::error('Validation failed for StoreUserRequest', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation'])
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
