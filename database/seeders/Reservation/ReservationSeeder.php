<?php

namespace Database\Seeders\Reservation;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tables = Table::all();
      

      
        for ($i = 0; $i < 8; $i++) {
            $startDate = Carbon::now()->addDays(rand(1, 30))->addHours(rand(0, 12));
            $endDate = (clone $startDate)->addHours(rand(1, 4));
            
            Reservation::create([
                'user_id' => $users->random()->id,
                'manager_id' => 1,
                'table_id' => $tables->random()->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'guest_count' => rand(1, 10),
                'status' => collect(['pending', 'confirmed', 'cancelled', 'in_service', 'completed', 'rejected'])->random(),
                'cancelled_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'email_sent_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
            ]);
        }
    }
}
