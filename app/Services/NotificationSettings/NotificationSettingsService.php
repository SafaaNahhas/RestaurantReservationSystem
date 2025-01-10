<?php

namespace App\Services\NotificationSettings;

use App\Http\Resources\NotificationSettings\NotificationSettingsResource;
use App\Models\NotificationSettings;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationSettingsService
{
    /**
     * check if notification settings exsits
     */
    public function checkIfNotificationSettingsExsits()
    {
        try {
            $notificationSettings = NotificationSettings::where('user_id', '=', Auth::user()->id)->latest()->first();
            if (!$notificationSettings) {
                return  [
                    'status' => 400,
                    'message' => "You haven't selected your notification settings yet.",
                ];
            } else {
                $notificationSettings = NotificationSettingsResource::make($notificationSettings);
                return  [
                    'status' => 200,
                    'message' => "get notification settings successfully",
                    'notificationSettings' => $notificationSettings
                ];
            }
        } catch (Exception $e) {
            Log::error("error create notification settings" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * create notification settings
     * @param   array  $notificationSettingsdata 
     * @return NotificationSettingsResource notificationSettings
     */
    public function createNotificationSettings($notificationSettingsdata)
    {
        try {
            $notificationSettings = NotificationSettings::create([
                'method_send_notification' => $notificationSettingsdata['method_send_notification'],
                'telegram_chat_id' => $notificationSettingsdata['method_send_notification'] == "telegram" ? $notificationSettingsdata['telegram_chat_id'] : null,
                'reservation_send_notification' => $notificationSettingsdata['reservation_send_notification'],
                'user_id' => Auth::user()->id
            ]);
            $notificationSettings = NotificationSettingsResource::make($notificationSettings);
            return  $notificationSettings;
        } catch (Exception $e) {
            Log::error("error create notification settings" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * update notification settings
     * @param   array  $notificationSettingsdata 
     * @return array status + message
     */
    public function updateNotificationSettings($notificationSettingsdata)
    {
        try {
            $notificationSettings = NotificationSettings::where('user_id', '=', Auth::user()->id)->latest()->first();

            if (!$notificationSettings) {
                return  [
                    'status' => 400,
                    'message' => "You haven't selected your notification settings yet.",
                ];
            } else {
                $notificationSettings->update([
                    'method_send_notification' => $notificationSettingsdata['method_send_notification'],
                    'telegram_chat_id' => $notificationSettingsdata['method_send_notification'] == "telegram" ? $notificationSettingsdata['telegram_chat_id'] : null,
                    'reservation_send_notification' => $notificationSettingsdata['reservation_send_notification'],
                ]);
                $notificationSettings = NotificationSettingsResource::make($notificationSettings);

                return  [
                    'status' => 200,
                    'message' => "Update Notification Settings Successfully",
                    'notificationSettings' => $notificationSettings
                ];
            }
        } catch (Exception $e) {
            Log::error("error update notification settings" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * reset notification settings
     */
    public function resetNotificationSettings()
    {
        try {
            $notificationSettings = NotificationSettings::where('user_id', '=', Auth::user()->id)->latest()->first();
            if (!$notificationSettings) {
                return  [
                    'status' => 400,
                    'message' => "You haven't selected your notification settings yet.",
                ];
            } else {
                $notificationSettings->delete();
                return  [
                    'status' => 200,
                    'message' => "Reset Notification Settings Successfully",
                ];
            }
        } catch (Exception $e) {
            Log::error("error reset notification settings" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
}