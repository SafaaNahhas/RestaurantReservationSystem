<?php

namespace App\Http\Requests\Permission;

use App\Services\Permissions\PermissionRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class   StorePermissionRequest extends FormRequest
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
        return [
            'name' => ['required', 'min:3', 'max:255 ', 'unique:permissions,name', 'string'],
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
