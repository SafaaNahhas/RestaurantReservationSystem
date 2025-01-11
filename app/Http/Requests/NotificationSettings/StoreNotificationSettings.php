<?php

namespace App\Http\Requests\NotificationSettings;

use App\Rules\CheckMethodSendNotification;
use App\Rules\CheckSendNotificationOptions;
use App\Services\NotificationSettings\NotificationSettingsRequestService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationSettings extends FormRequest
{
    protected $notificationSettingsRequestService;
    public function __construct(NotificationSettingsRequestService $notificationSettingsRequestService)
    {
        $this->notificationSettingsRequestService = $notificationSettingsRequestService;
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
        return [
            'method_send_notification' => ['required', 'string', new CheckMethodSendNotification],
            'telegram_chat_id' => ['sometimes', 'nullable', 'integer'],
            'send_notification_options' => ['required', 'array', new CheckSendNotificationOptions],
        ];
    }

    public function attributes(): array
    {
        return  $this->notificationSettingsRequestService->attributes();
    }
    public function failedValidation(Validator $validator)
    {
        $this->notificationSettingsRequestService->failedValidation($validator);
    }
}