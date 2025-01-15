<?php

namespace Database\Seeders\Favorite;

use App\Models\Favorite;
use App\Models\Dish;
use App\Models\FoodCategory;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites = [
            [
                'user_id' => 3,
                'favorable_type' => Table::class,
                'favorable_id' => 1
            ],
            [
                'user_id' => 4,
                'favorable_type' => FoodCategory::class,
                'favorable_id' => 1
            ],
        ];


        foreach ($favorites as $favorite) {
            Favorite::create($favorite);
        }
    }
}
