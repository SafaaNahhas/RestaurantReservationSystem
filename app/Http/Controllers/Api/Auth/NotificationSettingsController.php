<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\NotificationSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationSettings\StoreNotificationSettings;
use App\Http\Requests\NotificationSettings\UpdateNotificationSettings;
use App\Services\NotificationSettings\NotificationSettingsService;

class NotificationSettingsController extends Controller
{

    protected $notificationSettingsService;
    public function __construct(NotificationSettingsService $notificationSettingsService)
    {
        $this->notificationSettingsService = $notificationSettingsService;
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * check if notification settings exsits
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIfNotificationSettingsExsits()
    {
        $data = $this->notificationSettingsService->checkIfNotificationSettingsExsits();
        if ($data['status'] == 200) {
            return $this->success($data['notificationSettings'], $data['message'],  $data['status']);
        } else {
            return $this->error(message: $data['message'],    status: $data['status']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * create notification settings
     * @param StoreNotificationSettings $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreNotificationSettings $request)
    {
        $notificationSettingsdata = $request->validated();
        $notificationSettings =  $this->notificationSettingsService->createNotificationSettings($notificationSettingsdata);
        return $this->success($notificationSettings, "Create Notification Settings Successfully", 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * update notification settings
     * @param StoreNotificationSettings $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateNotificationSettings $request)
    {
        $notificationSettingsdata = $request->validated();
        $data =    $this->notificationSettingsService->updateNotificationSettings($notificationSettingsdata);
        if ($data['status'] == 200) {
            return $this->success($data['notificationSettings'], $data['message'],  $data['status']);
        } else {
            return $this->error(message: $data['message'],    status: $data['status']);
        }
    }
    /**
     * Store a newly created resource in storage.
     */

    /**
     * reset notification settings
     * @return \Illuminate\Http\JsonResponse
     */

    public function resetNotificationSettings()
    {
        $data = $this->notificationSettingsService->resetNotificationSettings();
        if ($data['status'] == 200) {
            return $this->success(message: $data['message'],  status: $data['status']);
        } else {
            return $this->error(message: $data['message'],    status: $data['status']);
        }
    }
}
