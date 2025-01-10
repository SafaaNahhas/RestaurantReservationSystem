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
      
            
            $Reservations=[
                [
                'user_id' => 4,
                'manager_id' => 1, 
                'table_id' => 2,
                'start_date' => '2025-01-20 20:10:00',
                'end_date' => '2025-01-20 22:10:00',
                'guest_count' => 5,
                'status' => 'pending',
                ],
                [
                'user_id' => 5,
                'manager_id' => 2, 
                'table_id' => 4,
                'start_date' => '2025-01-22 20:10:00',
                'end_date' => '2025-01-22 22:10:00',
                'guest_count' => 5,
                'status' => 'pending',
                ],

            ];
           
            foreach ($Reservations as $Reservation) {
                Reservation::create($Reservation);
            }
        
        }
    }

