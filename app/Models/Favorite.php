<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'favorable_type', 'favorable_id'];

    public function favorable()
    {
        return $this->morphTo();
    }
}
