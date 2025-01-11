<?php

namespace App\Services\NotificationSettings;

use Illuminate\Http\Exceptions\HttpResponseException;

class NotificationSettingsRequestService
{
    /**
     *  get array of  NotificationSettingsRequestService attributes 
     *
     * @return array   of attributes
     */
    public function attributes()
    {
        return  [
            'method_send_notification',
            'telegram_chat_id',
            'send_notification_options',
        ];
    }

    /**
     * 
     *  
     * @param $validator
     *
     * throw a exception
     */
    public function failedValidation($validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 'error',
                'message' => "Validation failed Please make sure that the values entered are correct",
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}
