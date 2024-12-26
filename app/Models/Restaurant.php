<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location', 'opening_hours', 'closing_hours', 'rating', 'website', 'description',
    ];


    public function images()
    {
        return $this->morphMany(Image::class, 'imagable');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }

    public function emails()
    {
        return $this->hasMany(Email::class);
    }
}
