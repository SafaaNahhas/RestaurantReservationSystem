<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationSettings
 *
 * Represents the notification settings for a user, including notification method and options.
 *
 * @package App\Models
 *
 * */
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['send_notification_options' => 'array'];

    /**
     * Relationship: Notification settings belong to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
