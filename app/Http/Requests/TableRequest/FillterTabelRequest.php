<?php

namespace App\Http\Requests\TableRequest;

use App\Services\TableRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class FillterTabelRequest extends FormRequest
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
            'table_number' => ['sometimes', 'string',],
            'location' => ['sometimes', 'string'],
            'seat_count' => ['sometimes', 'integer'],
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
