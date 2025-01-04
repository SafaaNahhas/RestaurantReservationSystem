<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'email_type', 'status', 'description'];


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

    // نطاق البحث عن طريق نوع البريد الإلكتروني
    public function scopeByEmailType($query, $email_type)
    {
        if ($email_type) {
            return $query->where('email_type', $email_type);
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
