<?php
namespace Database\Seeders\Food;

use App\Models\Dish;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        $dishes = [
            [
                'name' => 'Spaghetti Carbonara',
                'description' => 'Classic Italian pasta with eggs, cheese, pancetta, and pepper.',
                'category_id' => 1,
            ],
            [
                'name' => 'Sweet and Sour Chicken',
                'description' => 'Popular Chinese dish with crispy chicken in a tangy sauce.',
                'category_id' => 2,
            ],
        ];

        foreach ($dishes as $dish) {
            Dish::firstOrCreate(['name' => $dish['name']], $dish);
        }
    }
}
