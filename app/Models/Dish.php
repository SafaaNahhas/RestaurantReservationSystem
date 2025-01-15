<?php

namespace App\Models;

use App\Models\Image;
use App\Models\FoodCategory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Dish
 *
 * Represents a dish entity in the application.
 *
 * @package App\Models
 *
 * */
class Dish extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Mass-assignable attributes.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'description', 'category_id'];

    /**
     * Relationship: A dish has one image (Morph One).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imagable');
    }

    /**
     * Relationship: A dish belongs to a food category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }
}
