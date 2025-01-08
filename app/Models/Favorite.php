<?php

namespace App\Models;

use App\Models\Table;
use App\Models\FoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'favorable_type', 'favorable_id'];

    public function favorable()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter favorites by type.
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
