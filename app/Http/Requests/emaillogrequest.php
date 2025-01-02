<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class emaillogrequest extends FormRequest
{
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
           'status' => 'nullable|string|in:sent,failed',
            'created_at' => 'nullable|date',
            'email_type' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'emaillog_id' => 'nullable|integer|exists:email_logs,id',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
