<?php
namespace Database\Factories;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'event_name' => $this->faker->sentence,
            'start_date' => $this->faker->dateTimeBetween('now', '+1 hour'),
            'end_date' => $this->faker->dateTimeBetween('+1 hour', '+2 hours'),
            'details' => $this->faker->address,
            ];
    }
}
