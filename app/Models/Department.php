<?php

namespace App\Models;

use App\Models\User;
use App\Models\Image;
use App\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Department
 *
 * Represents a department entity in the application.
 *
 * @package App\Models
 * */
class Department extends Model
{
    use HasFactory;
    use SoftDeletes;


    /**
     * Mass-assignable attributes.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'description', 'manager_id'];

    /**
     * Relationship: A department belongs to a manager (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relationship: A department has one image (Morph One).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imagable');
    }

    /**
     * Relationship: A department has many tables.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
