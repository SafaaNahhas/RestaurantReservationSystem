<?php

namespace Database\Seeders\Event;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Reservation;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      

        Event::create([
            'event_name' => 'New Year',
            'start_date' => '2025-01-01 20:00:00', 
            'end_date' => '2025-01-01 23:59:59',  
            'details' => 'A New Year\'s Eve concert',
        ]);

        Event::create([
            'event_name' => 'Valentine\'s Day',
            'start_date' => '2025-02-14 18:00:00', 
            'end_date' => '2025-02-14 22:00:00',  
            'details' => 'Valentine\'s Day special dinner event',
        ]);

    }
}
