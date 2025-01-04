<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\EmailLog;
use Illuminate\Http\Request;
use App\Services\EmailLogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailLogRequest;
use App\Http\Resources\EmailLogResource;

class EmailLogController extends Controller
{
    protected $emailLogService;

    public function __construct(EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
    }

    public function index(EmailLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Retrieve email logs based on the validated data
        $emailLogs = $this->emailLogService->listemailgetlog($validatedData);

        // Return the paginated response of the retrieved email logs
        return self::paginated($emailLogs, EmailLogResource::class, 'Email logs retrieved successfully.', 200);
    }

    public function deleteEmailLogs(EmailLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to delete email logs based on the validated data
        $this->emailLogService->deleteEmailLogs($validatedData);

        // Return a successful response
        return self::success(null, 'Email logs deleted successfully.');
    }

    public function getDeletedEmailLogs(EmailLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to get soft-deleted email logs based on the validated data
        $deletedEmailLogs = $this->emailLogService->getDeletedEmailLogs($validatedData);

        // Return the paginated response of the retrieved soft-deleted email logs
        return self::paginated($deletedEmailLogs, EmailLogResource::class, 'Deleted email logs retrieved successfully.', 200);
    }

    public function restoreEmailLog(EmailLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to restore the soft-deleted email log
        $this->emailLogService->restoreDeletedEmailLog($validatedData);

        // Return a successful response
        return self::success(null, 'Email log restored successfully.');
    }

    public function permanentlyDeleteEmailLog(EmailLogRequest $request)
    {
        // Validate and retrieve the validated input data
        $validatedData = $request->validated();

        // Call the service method to permanently delete the soft-deleted email log
        $this->emailLogService->permanentlyDeleteEmailLog($validatedData);

        // Return a successful response
        return self::success(null, 'Email log permanently deleted.');
    }
}
