<?php

namespace App\Models;

use App\Models\Department;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

}
