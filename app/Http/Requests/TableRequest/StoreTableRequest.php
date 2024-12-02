<?php

namespace App\Http\Requests\TableRequest;

use App\Services\TableRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    protected $tableRequestService;
    public function __construct(TableRequestService $tableRequestService)
    {
        $this->tableRequestService = $tableRequestService;
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
            'table_number' => ['required', 'string', 'min:2', 'max:255', 'unique:tables,table_number'],
            'location' => ['required', 'string', 'min:6', 'max:255'],
            'seat_count' => ['required', 'integer', 'gt:0'],
            'department_id' => ['required', 'exists:departments,id', 'gt:0']
        ];
    }
    public function attributes(): array
    {
        return  $this->tableRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->tableRequestService->failedValidation($validator);
    }
}
