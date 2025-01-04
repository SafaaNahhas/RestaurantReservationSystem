<?php

namespace App\Services;

use Exception;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmailLogService
{
    /**
     * Retrieve a paginated list of email logs based on filters.
     *
     * @param array $data - The filters for the email logs (status, created_at, email_type, user_id).
     * @return \Illuminate\Pagination\LengthAwarePaginator - A paginated list of email logs.
     * @throws HttpResponseException - If an error occurs during the process.
     */
    public function listemailgetlog(array $data)
    {
        try {
            $emailLogs = EmailLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byEmailType($data['email_type'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->paginate(10);

            return $emailLogs;
        } catch (Exception $e) {
            Log::error('Error getting email log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error getting email log',
            ], 500));
        }
    }

    /**
     * Create a new email log entry.
     *
     * @param int $user_id - The ID of the user for the email log.
     * @param string $email_type - The type of email (e.g., 'Event Creation').
     * @param string $description - A brief description of the email action.
     * @return \App\Models\EmailLog - The created email log instance.
     * @throws HttpResponseException - If an error occurs during creation.
     */
    public function createEmailLog($user_id, $email_type, $description)
    {
        try {
            $emailLog = EmailLog::create([
                'user_id' => $user_id,
                'email_type' => $email_type,
                'status' => 'sent',
                'description' => $description
            ]);

            return $emailLog;
        } catch (Exception $e) {
            Log::error('Error creating email log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error creating email log',
            ], 500));
        }
    }

    /**
     * Update the status and description of an existing email log.
     *
     * @param \App\Models\EmailLog $emailLog - The existing email log to update.
     * @param string $description - The description to update the email log with.
     * @throws HttpResponseException - If an error occurs during the update.
     */
    public function updateEmailLog(EmailLog $emailLog, $description)
    {
        try {
            $emailLog->update([
                'status' => 'failed',
                'description' => $description,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating email log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error updating email log',
            ], 500));
        }
    }

    /**
     * Delete email logs based on provided filters.
     *
     * @param array $data - The filters for the email logs (status, created_at, email_type, user_id).
     * @throws HttpResponseException - If an error occurs during deletion.
     */
    public function deleteEmailLogs(array $data)
    {
        try {
            $emailLogs = EmailLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byEmailType($data['email_type'] ?? null)
                ->byUserId($data['user_id'] ?? null);

            $emailLogs->delete();
        } catch (Exception $e) {
            Log::error('Error deleting email logs: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error deleting email logs',
            ], 500));
        }
    }

    /**
     * Retrieve a list of soft-deleted email logs based on filters.
     *
     * @param array $data - The filters for the email logs (status, created_at, email_type, user_id).
     * @return \Illuminate\Database\Eloquent\Collection - A collection of soft-deleted email logs.
     * @throws HttpResponseException - If an error occurs during retrieval.
     */
    public function getDeletedEmailLogs(array $data)
    {
        try {
            $emailLogs = EmailLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byEmailType($data['email_type'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->onlyTrashed()
                ->paginate(10);

            return $emailLogs;
        } catch (Exception $e) {
            Log::error('Error getting deleted email logs: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error getting deleted email logs',
            ], 500));
        }
    }

    /**
     * Restore a soft-deleted email log by its filters.
     *
     * @param array $data - The filters to identify the email log.
     * @return bool - True if the email log was restored successfully.
     * @throws HttpResponseException - If an error occurs during restoration.
     */
    public function restoreDeletedEmailLog(array $data)
    {
        try {
            $emailLogs = EmailLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byEmailType($data['email_type'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->byId($data['emaillog_id'] ?? null)
                ->onlyTrashed();

            if ($emailLogs->count() > 0) {
                $emailLogs->restore();
                return true;
            } else {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            Log::error('Email log not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Email log not found',
            ], 500));
        } catch (Exception $e) {
            Log::error('Error restoring email log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error restoring email log',
            ], 500));
        }
    }

    /**
     * Permanently delete a soft-deleted email log by its filters.
     *
     * @param array $data - The filters to identify the email log.
     * @return bool - True if the email log was deleted permanently.
     * @throws HttpResponseException - If an error occurs during deletion.
     */
    public function permanentlyDeleteEmailLog(array $data)
    {
        try {
            $emailLogs = EmailLog::byStatus($data['status'] ?? null)
                ->byCreated($data['created_at'] ?? null)
                ->byEmailType($data['email_type'] ?? null)
                ->byUserId($data['user_id'] ?? null)
                ->byId($data['emaillog_id'] ?? null)
                ->onlyTrashed();

            if ($emailLogs->count() > 0) {
                $emailLogs->forceDelete();
                return true;
            } else {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            Log::error('Email log not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Email log not found',
            ], 500));
        } catch (Exception $e) {
            Log::error('Error permanently deleting email log: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error permanently deleting email log',
            ], 500));
        }
    }
}
