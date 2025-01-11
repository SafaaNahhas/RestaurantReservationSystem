<?php

namespace App\Observers;

use App\Models\PhoneNumber;
use Illuminate\Support\Facades\Cache;

class PhoneNumberObserver
{

    public function created(PhoneNumber $puonennumber)
    {
        Cache::forget('restaurant');
    }

    public function updated(PhoneNumber $puonennumber)
    {
        Cache::forget('restaurant');
    }

    public function deleted(PhoneNumber $puonennumber)
    {
        Cache::forget('restaurant');
    }

    public function forceDeleted(PhoneNumber $puonennumber)
    {
        Cache::forget('restaurant');
    }
}
