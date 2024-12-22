<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    protected $fillable=[
'email',
'description',
 'restaurant_id'];
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
