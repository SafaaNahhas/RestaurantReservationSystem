<?php

namespace App\Http\Requests\ReservationRequest;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
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
            'cancellation_reason' => 'required|string|max:255',
        ];
    }
    /**
     * Custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'cancellation_reason.required' => 'The cancellation reason is required.',
            'cancellation_reason.string' => 'The cancellation reason must be a valid string.',
            'cancellation_reason.max' => 'The cancellation reason must not exceed 255 characters.',
        ];
    }
}
