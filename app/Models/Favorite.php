<?php

namespace App\Models;

use App\Models\Table;
use App\Models\FoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Favorite
 *
 * Represents a polymorphic favorite entity in the application, allowing users to mark different types of entities as favorites.
 *
 * @package App\Models
 */
class Favorite extends Model
{
    use HasFactory;
    use SoftDeletes;



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'favorable_type', 'favorable_id'];


    /**
     * Morph relationship to link with different models such as tables and food categories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function favorable()
    {
        return $this->morphTo();
    }


    /**
     * Scope to filter favorites by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        $map = [
            'tables' => Table::class,
            'food' => FoodCategory::class,
        ];

        if (array_key_exists($type, $map)) {
            return $query->where('favorable_type', $map[$type]);
        }

        return $query;
    }
}
