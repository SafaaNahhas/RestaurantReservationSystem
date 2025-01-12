<?php

namespace App\Http\Controllers\Api\Notification;

use App\Services\NotificationLogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationLogRequest;
use App\Http\Resources\Notification\NotificationLogResource;
use App\Models\NotificationLog;

class NotificationLogController extends Controller
{
    protected $notificationLogService;

    public function __construct(NotificationLogService $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
    }
    public function index(NotificationLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Retrieve notification logs based on the validated data
        $notificationLogs = $this->notificationLogService->getDeletedNotificationLogs($validatedData);

        // Return the paginated response of the retrieved notification logs
        return self::paginated($notificationLogs, NotificationLogResource::class, 'Notification logs retrieved successfully.', 200);
    }
    public function show(int $notificationLog_id)
    {
        $notificationLog = $this->notificationLogService->getNotificationLog($notificationLog_id);

        return $this->success($notificationLog, 'Get Notification Log Successfully.', 200);
    }
    public function deleteNotificationLogs(NotificationLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to delete notification logs based on the validated data
        $this->notificationLogService->deleteNotificationLogs($validatedData);

        // Return a successful response
        return self::success(null, 'Notification logs deleted successfully.');
    }

    public function getDeletedNotificationLogs(NotificationLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to get soft-deleted notification logs based on the validated data
        $deletedNotificationLogs = $this->notificationLogService->getDeletedNotificationLogs($validatedData);

        // Return the paginated response of the retrieved soft-deleted notification logs
        return self::paginated($deletedNotificationLogs, NotificationLogResource::class, 'Deleted notification logs retrieved successfully.', 200);
    }

    public function restoreNotificationLog(NotificationLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to restore the soft-deleted notification log
        $this->notificationLogService->restoreDeletedNotificationLog($validatedData);

        // Return a successful response
        return self::success(null, 'Notification log restored successfully.');
    }

    public function permanentlyDeleteNotificationLog(NotificationLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to permanently delete the soft-deleted notification log
        $this->notificationLogService->permanentlyDeleteNotificationLog($validatedData);

        // Return a successful response
        return self::success(null, 'Notification log permanently deleted.');
    }
}