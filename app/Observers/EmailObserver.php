<?php
namespace App\Observers;

use App\Models\Email;
use Illuminate\Support\Facades\Cache;

class EmailObserver
{
    public function created(Email $email)
    {
        Cache::forget('restaurant');
    }

    public function updated(Email $email)
    {
        Cache::forget('restaurant');
    }

    public function deleted(Email $email)
    {
        Cache::forget('restaurant');
    }

    public function forceDeleted(Email $email)
    {
        Cache::forget('restaurant');
    }
}
