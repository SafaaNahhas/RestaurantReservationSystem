<?php

namespace Database\Seeders\Rating;

use App\Models\Rating;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $ratings = [
            [
                'user_id' => 3,
                'reservation_id' => 2, 
                'rating' => 5,
            ],
            [
                'user_id' => 4,
                'reservation_id' => 1,
                'rating' => 4,
            ], 
        ];

        
        foreach ($ratings as $rating) {
            Rating::create($rating);
        }
    }
}
