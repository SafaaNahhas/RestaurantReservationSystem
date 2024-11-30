<?php

namespace App\Http\Requests\TableRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Services\TableRequestService;

class UpdateTableRequest extends FormRequest
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
        $id = $this->route('table');
        return [
            'table_number' => ['sometimes', 'string', 'min:2', 'max:255', 'unique:tables,table_number,' . $id],
            'location' => ['sometimes', 'string', 'min:6', 'max:255'],
            'seat_count' => ['sometimes', 'integer', 'gt:0'],
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