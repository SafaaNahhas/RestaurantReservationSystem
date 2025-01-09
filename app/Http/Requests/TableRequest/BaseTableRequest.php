<?php

namespace App\Http\Requests\TableRequest;

use App\Services\Tables\TableRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseTableRequest extends FormRequest
{
    protected TableRequestService $tableRequestService;

    /**
     * Constructor for BaseTableRequest
     *
     * Initializes the request with TableRequestService dependency
     * through Laravel dependency injection.
     *
     * @param TableRequestService $tableRequestService Service for handling table validations
     */
    public function __construct(TableRequestService $tableRequestService)
    {
        parent::__construct();
        $this->tableRequestService = $tableRequestService;
    }

    /**
     * Determine if the user is authorized to make this request
     *
     * Currently, allows all requests. Override in child classes
     * if specific authorization is needed.
     *
     * @return bool Always returns true by default
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom attributes for validator errors
     *
     * @return array<string, string> Array of attribute names
     */
    public function attributes(): array
    {
        return $this->tableRequestService->attributes();
    }

    /**
     * Get the validation error messages
     *
     * @return array<string, string> Array of error messages
     */
    public function messages(): array
    {
        return $this->tableRequestService->getValidationMessages();
    }

    /**
     * Handle a failed validation attempt
     *
     * @param Validator $validator The validator instance containing the failed validation
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $this->tableRequestService->failedValidation($validator);
    }
}
