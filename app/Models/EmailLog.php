<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    // Mass-assignable attributes
    protected $fillable = ['user_id', 'email_type', 'status'];
    /**
     * Relationship: An email log belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
