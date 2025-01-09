<?php

namespace App\Http\Requests\Role;

use App\Services\Permissions\PermissionRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AddPermissionsRequest extends FormRequest
{
    protected $permissionRequestService;
    public function __construct(PermissionRequestService $permissionRequestService)
    {
        $this->permissionRequestService = $permissionRequestService;
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
            'permissions' => ['required', 'array']
        ];
    }

    public function attributes(): array
    {
        return  $this->permissionRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->permissionRequestService->failedValidation($validator);
    }
}