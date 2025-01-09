<?php
namespace Database\Seeders\Food;

use App\Models\FoodCategory;
use Illuminate\Database\Seeder;

class FoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Italian',
                'description' => 'Authentic Italian dishes.',
                'user_id' => 1,
            ],
            [
                'category_name' => 'Chinese',
                'description' => 'Delicious Chinese dishes.',
                'user_id' => 1,
            ],
        ];

        foreach ($categories as $category) {
            FoodCategory::firstOrCreate(['category_name' => $category['category_name']], $category);
        }
    }
}
