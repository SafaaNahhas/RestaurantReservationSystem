<?php

namespace App\Models;

use App\Models\Image;
use App\Models\FoodCategory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dish extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['name', 'description', 'image_id', 'category_id'];
    /**
     * Relationship: A department has one image (Morph One).
     */
    public function image()
    {
        return $this->morphOne(Image::class, 'imagable');
    }
    /**
     * Relationship: A dish belongs to a food category.
     */
    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }


 
}
