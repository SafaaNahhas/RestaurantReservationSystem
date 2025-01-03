<?php

namespace App\Http\Requests\Role;

use App\Rules\NotAdmin;
use App\Rules\UserRole;
use App\Services\RoleRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    protected $roleRequestService;
    public function __construct(RoleRequestService $roleRequestService)
    {
        $this->roleRequestService = $roleRequestService;
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
        $role = $this->route('role');
        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255', "unique:roles,name,$role"],
         ];
    }

    public function attributes(): array
    {
        return  $this->roleRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->roleRequestService->failedValidation($validator);
    }
}