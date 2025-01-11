<?php

namespace App\Http\Requests\Payment;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class ProcessPaymentRequest extends FormRequest
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
    public function rules()
    {
        return [

            'amount' => 'required|numeric|min:0.5',
            'stripeToken' => 'required|string',
            'reservation_id' => 'required|integer|exists:reservations,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            //amount error messages
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a numeric.',
            'amount.min:0.5' => 'The amount must be more then 0.5.',

            // stripeToken error messages
            'stripeToken.string' => 'The stripeToken must be string.',
            'stripeToken.required' => 'The stripeToken is required.'
        ];
    }

    // protected function passedValidation(): void
    // {
    //     // Log successful validation with payment submission details
    //     Log::info('Store Rating form validation passed', [
    //         'user_id' => auth()->id(),                                   // Rating creator
    //         'reservation_id' => $this->query('reservation_id'),     // Associated reservation
    //         'rating' => $this->rating,                                   // Numerical rating value
    //         'comment' => $this->comment,                                 // Optional feedback text
    //         'ip' => $this->ip(),                                         // Client IP for audit trail
    //         'user_agent' => $this->userAgent(),                          // Browser/device information
    //     ]);
    // }

    protected function failedValidation(Validator $validator): void
    {
        // Log validation failure with context
        Log::warning('payment form validation failed', [
            'errors' => $validator->errors()->toArray(),                 // Validation error details

        ]);

        // Return standardized error response
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Payment validation failed',
            'errors' => $validator->errors()
        ], 422));
    }

}
