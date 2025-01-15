<?php

namespace App\Services;

use App\Http\Resources\Notification\NotificationLogResource;
use Exception;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationLogService
{
    /**
     * Retrieve a paginated list of notification logs based on filters.
     *
     * @param array $data - The filters for the notification logs (status, created_at, notification_method,reason_notification_send, user_id).
     * @return LengthAwarePaginator - A paginated list of notification logs.
     * @throws HttpResponseException - If an error occurs during the process.
     */
    public function getNotificationslog(array $data)
    {
        try {
            $notificationLogs = NotificationLog::query()->byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byNotificationMethod($data['notification_method'] ?? null)
                ->byResonNotificationSend($data['reason_notification_send'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->paginate(10);
            return $notificationLogs;
        } catch (Exception $e) {
            Log::error('Error getting notifications log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error getting notifications log',
            ], 500));
        }
    }
    public function getNotificationLog(int $notificationLog_id)
    {
        try {
            $notificationLog = NotificationLog::findOrFail($notificationLog_id);
            $notificationLog = NotificationLogResource::make($notificationLog);
            return $notificationLog;
        } catch (ModelNotFoundException $e) {
            Log::error('Error get notification log: ' . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error('Error getting notifications log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error getting notifications log',
            ], 500));
        }
    }

    /**
     * Create a new notification log entry.
     *
     * @param int $user_id - The ID of the user for the notification log.
     * @param string $notification_method - The type of notification
     * @param string  $reason_notification_send  The reason of sending notification  (e.g., 'Event Creation').
     * @param string $description - A brief description of the notification action.
     * @return  NotificationLog - The created notification log instance.
     * @throws HttpResponseException - If an error occurs during creation.
     */
    public function createNotificationLog($user_id, $notification_method, $reason_notification_send, $description)
    {
        try {
            $notificationLogs = NotificationLog::create([
                'user_id' => $user_id,
                'notification_method' => $notification_method,
                'reason_notification_send' => $reason_notification_send,
                'status' => 'sent',
                'description' => $description
            ]);
            return $notificationLogs;
        } catch (Exception $e) {
            Log::error('Error creating notification log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error creating notification log',
            ], 500));
        }
    }

    /**
     * Update the status and description of an existing notification log.
     *
     * @param NotificationLog $emailLog - The existing notification log to update.
     * @param string $description - The description to update the notification log with.
     * @throws HttpResponseException - If an error occurs during the update.
     */
    public function updateNotificationLog($notificationLog, $description)
    {
        try {
            $notificationLog->update([
                'status' => 'failed',
                'description' => $description,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating notification log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error updating notification log',
            ], 500));
        }
    }

    /**
     * Delete notification logs
     * @param int $notificationLog_id
     * @throws HttpResponseException - If an error occurs during deletion.
     */
    public function deleteNotificationLogs(int $notificationLog_id)
    {
        try {
            $notificationLog = NotificationLog::findOrFail($notificationLog_id);
            $notificationLog->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('notification log not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'notification log not found',
            ], 404));
        } catch (Exception $e) {
            Log::error('Error deleting notification logs: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error deleting notification logs',
            ], 500));
        }
    }

    /**
     * Retrieve a list of soft-deleted notification logs based on filters.
     *
     * @param array $data - The filters for the notification logs (status, created_at, notification_method,reason_notification_send, user_id).
     * @return LengthAwarePaginator  A collection of soft-deleted notification logs.
     * @throws HttpResponseException - If an error occurs during retrieval.
     */
    public function getDeletedNotificationLogs(array $data)
    {
        try {
            $notificationLogs = NotificationLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byNotificationMethod($data['notification_method'] ?? null)
                ->byResonNotificationSend($data['reason_notification_send'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->onlyTrashed()
                ->paginate(10);
            return $notificationLogs;
        } catch (Exception $e) {
            Log::error('Error getting deleted notification logs: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error getting deleted notification logs',
            ], 500));
        }
    }

    /**
     * Restore a soft-deleted notification log
     * @param int $notificationLog_id
     * @retrun NotificationLogResource $notificationLog
     * @throws HttpResponseException - If an error occurs during restoration.
     */
    public function restoreDeletedNotificationLog(int $notificationLog_id)
    {
        try {
            $notificationLog = NotificationLog::onlyTrashed()->findOrFail($notificationLog_id);
            $notificationLog->restore();
            $notificationLog = NotificationLogResource::make($notificationLog);
            return $notificationLog;
        } catch (ModelNotFoundException $e) {
            Log::error('notification log not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'notification log not found',
            ], 404));
        } catch (Exception $e) {
            Log::error('Error restoring notification log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error restoring notification log',
            ], 500));
        }
    }

    /**
     * Permanently delete a soft-deleted notification log
     * @paramint $notificationLog_id
     * @throws HttpResponseException - If an error occurs during deletion.
     */
    public function permanentlyDeleteNotificationLog(int $notificationLog_id)
    {
        try {
            $notificationLog = NotificationLog::withTrashed()->findOrFail($notificationLog_id);
            $notificationLog->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error('notification log not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'notification log not found',
            ], 404));
        } catch (Exception $e) {
            Log::error('Error permanently deleting notification log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error permanently deleting notification log',
            ], 500));
        }
    }
}
