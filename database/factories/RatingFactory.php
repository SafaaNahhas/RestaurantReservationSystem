<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rating::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // يولد مستخدمًا جديدًا باستخدام Factory
            'reservation_id' => Reservation::factory(), // يولد حجزًا جديدًا باستخدام Factory
            'rating' => $this->faker->numberBetween(1, 5), // قيمة التقييم بين 1 و5
            'comment' => $this->faker->sentence(), // تعليق عشوائي
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
