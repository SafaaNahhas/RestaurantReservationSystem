<?php

namespace Database\Seeders\Favorite;

use App\Models\Favorite;
use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites = [
            [
                'user_id' => 4,  
                'favorable_type' => Restaurant::class,  
                'favorable_id' => 1  
            ],
            [
                'user_id' => 5,  
                'favorable_type' => Dish::class,
                'favorable_id' => 1  
            ],
        ];

        
        foreach ($favorites as $favorite) {
            Favorite::create($favorite);
        }
    }
}
