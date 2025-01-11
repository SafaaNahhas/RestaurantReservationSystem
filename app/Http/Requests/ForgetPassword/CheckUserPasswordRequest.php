<?php

namespace App\Http\Requests\ForgetPassword;

use Illuminate\Foundation\Http\FormRequest;
 use Illuminate\Contracts\Validation\Validator;
 use App\Services\ForgetPassword\ForgetPasswordRequestService;

class CheckUserPasswordRequest extends FormRequest
{
    protected $forgetPasswordRequestService;
    public function __construct(ForgetPasswordRequestService $forgetPasswordRequestService)
    {
        $this->forgetPasswordRequestService = $forgetPasswordRequestService;
    }
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
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }
    public function attributes(): array
    {
        return  $this->forgetPasswordRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->forgetPasswordRequestService->failedValidation($validator);
    }
}