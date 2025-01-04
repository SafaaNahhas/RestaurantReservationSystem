<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        return [
            'table_number' => $this->faker->unique()->word, // استخدم unique() لجعل القيم فريدة
            'location' => $this->faker->randomElement(['indoor', 'outdoor']),
            'seat_count' => $this->faker->numberBetween(2, 10),
        ];
    }
}
