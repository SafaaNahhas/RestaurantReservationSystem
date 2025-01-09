<?php

namespace Database\Factories;

use App\Models\Emergency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EmergencyFactory extends Factory
{
    protected $model = Emergency::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 week'); // تاريخ البدء بين الآن وأسبوع
        $endDate = (clone $startDate)->modify('+2 hours'); // تاريخ الانتهاء بعد ساعتين من تاريخ البدء

        return [
            'name' => fake()->name(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $this->faker->sentence(),

        ];
    }
}
