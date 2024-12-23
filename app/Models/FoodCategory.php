<?php

namespace App\Models;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['category_name', 'description', 'user_id'];
    /**
     * Relationship: A category belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relationship: A category has many dishes.
     */
    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }
}
