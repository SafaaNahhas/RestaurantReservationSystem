<?php

namespace App\Models;

use App\Models\Department;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Table extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['table_number', 'location', 'seat_count',  'department_id'];
    /**
     * Relationship: A table belongs to a department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: A table has many reservations.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeByTableNumber(Builder $query, $table_number)
    {
        if ($table_number)
            return $query->where('table_number', 'like', "%$table_number%");
        else
            return $query;
    }
    public function scopeBySeatCount(Builder $query, $seat_count)
    {
        if ($seat_count)
            return $query->where('seat_count', '=', $seat_count);
        else
            return $query;
    }

    public function scopeByLocation(Builder $query, $location)
    {
        if ($location)
            return $query->where('location', '=', "%$location%");
        else
            return $query;
    }
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }
}
