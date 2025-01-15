<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class NotificationLog
 *
 * Represents a log entry for notifications sent to users.
 *
 * @package App\Models
 *
 * */
class NotificationLog extends Model
{
    use HasFactory;
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'notification_method', 'reason_notification_send', 'status', 'description'];

    /**
     * Relationship: A notification log belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter notification logs by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        } else {
            return $query;
        }
    }

    /**
     * Scope to filter notification logs by notification method.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $notification_method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByNotificationMethod($query, $notification_method)
    {
        if ($notification_method) {
            return $query->where('notification_method', $notification_method);
        } else {
            return $query;
        }
    }

    /**
     * Scope to filter notification logs by the reason for sending the notification.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $reason_notification_send
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByResonNotificationSend($query, $reason_notification_send)
    {
        if ($reason_notification_send) {
            return $query->where('reason_notification_send', 'like', "%$reason_notification_send%");
        } else {
            return $query;
        }
    }

    /**
     * Scope to filter notification logs by creation date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $created_at
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreated($query, $created_at)
    {
        if ($created_at) {
            return $query->whereDate('created_at', $created_at);
        } else {
            return $query;
        }
    }

    /**
     * Scope to filter notification logs by user ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $user_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUserId($query, $user_id)
    {
        if ($user_id) {
            return $query->where('user_id', $user_id);
        } else {
            return $query;
        }
    }

    /**
     * Scope to filter notification logs by ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeById($query, $id)
    {
        if ($id) {
            return $query->where('id', $id);
        } else {
            return $query;
        }
    }
}
