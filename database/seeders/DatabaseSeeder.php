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

        Reservation::factory()->count(10)->create();
        Table::factory()->count(10)->create();
        Rating::factory()->count(10)->create();
        FoodCategory::factory()->count(10)->create();

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin4@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole(RoleUser::Admin);

        $category1 = FoodCategory::create([
            'category_name' => 'Italian',
            'description' => 'Authentic Italian dishes including pasta, pizza, and more.',
            'user_id' => $user->id,
        ]);

        $category2 = FoodCategory::create([
            'category_name' => 'Chinese',
            'description' => 'Delicious Chinese dishes like noodles, rice, and dumplings.',
            'user_id' => $user->id,
        ]);
        //create dishes
        Dish::create([
            'name' => 'Spaghetti Carbonara',
            'description' => 'Classic Italian pasta with eggs, cheese, pancetta, and pepper.',
            'category_id' => $category1->id,
        ]);

        Dish::create([
            'name' => 'Sweet and Sour Chicken',
            'description' => 'Popular Chinese dish with crispy chicken in a tangy sauce.',
            'category_id' => $category2->id,
        ]);
        // Create the first manager
        $manager1 = User::create([
            'name' => 'Manager One',
            'email' => 'managerone@example.com',
            'password' => '123456789'

        ]);
        // create department
        Department::create([
            'name' => 'Kitchen',
            'description' => 'Responsible for food preparation.',
            'manager_id' => $manager1->id,

        ]);
        $manager2 = User::create([
            'name' => 'Manager Two',
            'email' => 'managerTwo@example.com',
       'password' => '123456789'

        ]);
        Department::create([
            'name' => 'Service',
            'description' => 'Responsible for customer service.',
            'manager_id' => $manager2->id,
        ]);

        User::create([
            'name' => 'haidar',
            'email' => 'haidar@gmail.com',
            'phone' => '1234567890',
       'password' => '123456789',
            'is_active' => true,
        ]);
        //create favorite
        Favorite::factory()->count(10)->create();
    }
}
