<?php

namespace App\Http\Requests\ForgetPassword;

use App\Rules\CheckUserEmail;
 use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\ForgetPassword\ForgetPasswordRequestService;

class CheckUserEmailRequest extends FormRequest
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
            'email' => ['required', 'email', new CheckUserEmail],
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