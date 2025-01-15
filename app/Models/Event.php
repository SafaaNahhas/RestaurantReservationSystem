<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Event
 *
 * Represents an event in the application.
 *
 * @package App\Models
 *
 * */
class Event extends Model
{
    use HasFactory;
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['event_name', 'start_date', 'end_date', 'details'];
}
