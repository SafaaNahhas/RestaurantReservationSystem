<?php

namespace App\Http\Requests\NotificationLog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class NotificationLogRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:sent,failed',
            'created_at' => 'nullable|date',
            'notification_method' => 'nullable|string|max:255',
            'reason_notification_send' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * Handle successful validation for email log request.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        Log::info('Email Log form validation passed', [
            'status' => $this->status,           // Email delivery status
            'created_at' => $this->created_at,   // Email timestamp
            'notification_method' => $this->notification_method,   // Type of email sent
            'reason_notification_send' => $this->reason_notification_send,   // Type of email sent
            'user_id' => $this->user_id,         // Associated user
            'notification_id' => $this->notification_id, // Email log reference
            'ip' => $this->ip(),                 // Client IP for audit
            'user_agent' => $this->userAgent(),  // Browser/device info
            'timestamp' => now(),                // Server timestamp
        ]);
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
