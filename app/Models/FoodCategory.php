<?php

namespace App\Models;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class FoodCategory
 *
 * Represents a category of food, containing dishes and associated with a user.
 *
 * @package App\Models
 */
class FoodCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['category_name', 'description', 'user_id'];

    /**
     * Relationship: A category belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A category has many dishes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    /**
     * Relationship: A category can be favorited by many users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }
}
