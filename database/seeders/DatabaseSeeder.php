<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\RoleUser;
use App\Models\Dish;
use App\Models\User;
use App\Models\Rating;
use App\Models\Department;
use App\Models\Restaurant;
use App\Models\FoodCategory;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\RestaurantSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Hash;

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
            RestaurantSeeder::class,
        ]);

        \App\Models\Reservation::factory()->count(10)->create();
        \App\Models\Table::factory()->count(10)->create();
         Rating::factory()->count(10)->create();

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->assignRole(RoleUser::Admin);

        // إنشاء فئات الطعام
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

        // إنشاء أطباق طعام
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
            'email' => 'manager1@example.com',
            'password' => Hash::make('123456789')

        ]);
        // إنشاء أقسام
        Department::create([
            'name' => 'Kitchen',
            'description' => 'Responsible for food preparation.',
            'manager_id' => $manager1->id,

        ]);
        $manager2 = User::create([
            'name' => 'Manager Two',
            'email' => 'manager2@example.com',
            'password' => Hash::make('123456789')

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
            'password' => Hash::make(12345678),
            'is_active' => true,
        ]);

    }
}
