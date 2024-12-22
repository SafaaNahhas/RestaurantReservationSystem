<?php

namespace App\Services;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TableRequestService
{
    /**
     * Get common validation rules for table fields
     *
     * Defines the base validation rules that are shared across different
     * table operations (create, update, etc.).
     *
     * @return array<string, array> Array of validation rules for each field
     */
    public function getCommonRules(): array
    {
        return [
            'table_number' => ['string', 'min:2', 'max:255'],
            'location' => ['string', 'min:6', 'max:255'],
            'seat_count' => ['integer', 'gt:0'],
            'department_id' => ['integer', 'exists:departments,id', 'gt:0'],
        ];
    }

    /**
     * Get validation messages for table fields
     *
     * @return array<string, string> Array of validation messages
     */
    public function getValidationMessages(): array
    {
        return [
            // Table Number validation messages
            'table_number.required' => 'The :attribute is required.',
            'table_number.unique' => 'This :attribute is already taken.',
            'table_number.min' => 'The :attribute must be at least :min characters.',

            // Location validation messages
            'location.required' => 'Table :attribute is required.',
            'location.min' => 'The :attribute must be at least :min characters.',

            // Seat Count validation messages
            'seat_count.required' => 'The :attribute is required.',
            'seat_count.integer' => 'The :attribute must be a number.',
            'seat_count.gt' => 'The :attribute must be greater than 0.',

            // Department validation messages
            'department_id.required' => 'The :attribute is required.',
            'department_id.exists' => 'The Selected :attribute does not exist.',
        ];
    }

    /**
     * Get table attributes
     *
     * @return array<string, string> Array of field names
     */
    public function attributes(): array
    {
        return [
            'table_number' => 'Table Number',
            'location' => 'Location',
            'seat_count' => 'Seats',
            'department_id' => 'Department',
        ];
    }

    /**
     * Handle failed validation
     *
     * Throws an HttpResponseException with a JSON response containing
     * validation errors. Used by form requests when validation fails.
     *
     * @param Validator $validator The validator instance containing errors
     * @throws HttpResponseException
     * @return void
     */
    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed. Please check the provided data.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
