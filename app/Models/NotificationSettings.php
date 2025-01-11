<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'method_send_notification',
        'telegram_chat_id',
        'send_notification_options',
        'user_id'
    ];

    protected $casts = ['send_notification_options' => 'array'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
