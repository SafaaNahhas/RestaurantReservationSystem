<?php

namespace App\Models;

use App\Models\User;
use App\Models\Image;
use App\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['name', 'description', 'manager_id'];
    /**
     * Relationship: A department belongs to a manager (User).
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relationship: A department has one image (Morph One).
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imagable');
    }
    /**
     * Relationship: A department has many tables.
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

}
