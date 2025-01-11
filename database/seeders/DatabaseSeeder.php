<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Dish;
use App\Models\User;
use App\Models\Table;
use App\Models\Rating;
use App\Enums\RoleUser;
use App\Models\Favorite;
use App\Models\Department;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Models\FoodCategory;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\Food\DishSeeder;
use Database\Seeders\Event\EventSeeder;
use Database\Seeders\Rating\RatingSeeder;
use Database\Seeders\Favorite\FavoriteSeeder;
use Database\Seeders\Food\FoodCategorySeeder;
use Database\Seeders\Reservation\TableSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\Restaurant\DepartmentSeeder;
use Database\Seeders\Restaurant\RestaurantSeeder;
use Database\Seeders\Reservation\ReservationSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            FoodCategorySeeder::class,
            DishSeeder::class,
            TableSeeder::class,
            ReservationSeeder::class,
            RatingSeeder::class,
            FavoriteSeeder::class,
            EventSeeder::class,
            RestaurantSeeder::class,
        ]);
    }
}
