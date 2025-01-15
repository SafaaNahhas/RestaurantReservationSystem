<?php

namespace App\Models;

use App\Models\Department;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Table
 *
 * Represents a table in a restaurant, including its number, location, seat count, and associated department.
 * It supports relationships with reservations and can also be favorited by users.
 *
 * @package App\Models
 *
 *
 * */
class Table extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['table_number', 'location', 'seat_count',  'department_id'];

    /**
     * Get the department that the table belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the reservations associated with the table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope to filter tables by table number.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $table_number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTableNumber(Builder $query, $table_number)
    {
        if ($table_number)
            return $query->where('table_number', 'like', "%$table_number%");
        else
            return $query;
    }

    /**
     * Scope to filter tables by seat count.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $seat_count
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySeatCount(Builder $query, $seat_count)
    {
        if ($seat_count)
            return $query->where('seat_count', '=', $seat_count);
        else
            return $query;
    }

    /**
     * Scope to filter tables by location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $location
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLocation(Builder $query, $location)
    {
        if ($location)
            return $query->where('location', '=', "%$location%");
        else
            return $query;
    }

    /**
     * Get the favorite records associated with the table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }
}
