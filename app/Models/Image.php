<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['image_path','mime_type','name'];
    /**
     * Relationship: Supports morphable relations with multiple models.
     */
    public function imagable()
    {
        return $this->morphTo();
    }
}
