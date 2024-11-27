<?php

namespace App\Models;

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
    protected $fillable = ['name', 'description', 'image_id'];
    /**
     * Relationship: A department belongs to an image.
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
    /**
     * Relationship: A department has many tables.
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
