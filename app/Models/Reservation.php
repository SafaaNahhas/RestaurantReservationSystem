<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use App\Models\Table;
use App\Models\ReservationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    // use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['user_id', 'manager_id', 'table_id', 'start_date', 'end_date', 'guest_count', 'services', 'status', 'cancelled_at'];
    /**
     * Relationship: A reservation belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relationship: A reservation belongs to a manager.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    /**
     * Relationship: A reservation belongs to a table.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }
    /**
     * Relationship: A reservation has many logs.
     */
    public function reservationLogs()
    {
        return $this->hasMany(ReservationLog::class);
    }
    /**
     * Relationship: A reservation has many events.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function rating(){
        return $this->hasOne(Rating::class);
    }




}
