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
        'reservation_send_notification',
        'user_id'
     ];

     protected $casts = ['reservation_send_notification' => 'array'];


    public function user()
    {
        return $this->belongsTo(User::class );
    }

}
