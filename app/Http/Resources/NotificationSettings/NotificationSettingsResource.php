<?php

namespace App\Http\Resources\NotificationSettings;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'method_send_notification' => $this->method_send_notification,
            'telegram_chat_id' => $this->telegram_chat_id ?? '', 
            'reservation_send_notification' => $this->reservation_send_notification, 

        ];
    }
}