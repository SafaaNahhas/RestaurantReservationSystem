<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * Class Image
 *
 * Represents an image entity that can be associated with various models using polymorphic relationships.
 *
 * @package App\Models
 */
class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['image_path', 'mime_type', 'name'];

    /**
     * Relationship: Supports polymorphic relations with multiple models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imagable()
    {
        return $this->morphTo();
    }
}
