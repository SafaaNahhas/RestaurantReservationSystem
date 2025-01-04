<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use App\Models\Table;
use App\Models\ReservationLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    // use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['user_id', 'manager_id', 'table_id', 'start_date', 'end_date', 'guest_count', 'services', 'status', 'cancelled_at', 'email_sent_at',];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'email_sent_at' => 'datetime'
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

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

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    /**
     * Mutator for start_date attribute.
     * Formats the incoming date to 'YYYY-MM-DD HH:mm' format.
     *
     * @param mixed $value The input date value
     */
    public function setStartDateAttribute(mixed $value): void
    {
        $this->attributes['start_date'] = Carbon::parse($value)->format('Y-m-d H:i');
    }

    /**
     * Mutator for end_date attribute.
     * Formats the incoming date to 'YYYY-MM-DD HH:mm' format.
     * Returns null if no value is provided.
     *
     * @param mixed|null $value The input date value
     */
    public function setEndDateAttribute(mixed $value): void
    {
        $this->attributes['end_date'] = $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }

    /**
     * Accessor for start_date attribute.
     * Retrieves the date in 'YYYY-MM-DD HH:mm' format.
     *
     * @param mixed $value The stored date value
     * @return string|null Formatted date string or null if no date
     */
    public function getStartDateAttribute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }

    /**
     * Accessor for end_date attribute.
     * Retrieves the date in 'YYYY-MM-DD HH:mm' format.
     *
     * @param mixed $value The stored date value
     * @return string|null Formatted date string or null if no date
     */
    public function getEndDateAttribute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
