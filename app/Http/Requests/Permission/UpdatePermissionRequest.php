<?php

namespace App\Http\Requests\Permission;

use App\Rules\UserPermission;
use App\Services\PermissionRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    protected $permissionRequestService;
    public function __construct(PermissionRequestService $permissionRequestService)
    {
        $this->permissionRequestService = $permissionRequestService;
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
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
        $permission = $this->route('permission');
        return [
            'name' => ['sometimes', 'min:3', 'max:255 ', "unique:permissions,name,$permission", 'string'],
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