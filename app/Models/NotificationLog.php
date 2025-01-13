<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'notification_method', 'reason_notification_send', 'status', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        } else {
            return $query;
        }
    }

    // نطاق البحث عن طريق نوع  الاشعار
    public function scopeByNotificationMethod($query, $notification_method)
    {
        if ($notification_method) {
            return $query->where('notification_method', $notification_method);
        } else {
            return $query;
        }
    }
    public function scopeByResonNotificationSend($query, $reason_notification_send)
    {
        if ($reason_notification_send) {
            return $query->where('reason_notification_send', 'like', "%$reason_notification_send%");
        } else {
            return $query;
        }
    }



    // نطاق البحث عن طريق تاريخ الإنشاء
    public function scopeByCreated($query, $created_at)
    {
        if ($created_at) {
            return $query->whereDate('created_at', $created_at);
        } else {
            return $query;
        }
    }

    // نطاق البحث عن طريق معرف المستخدم
    public function scopeByUserId($query, $user_id)
    {
        if ($user_id) {
            return $query->where('user_id', $user_id);
        } else {
            return $query;
        }
    }
    public function scopeById($query, $id)
    {
        if ($id) {
            return $query->where('id', $id);
        } else {
            return $query;
        }
    }
}
