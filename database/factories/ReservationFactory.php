<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReservationFactory extends Factory
{
      /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 week'); // تاريخ البدء بين الآن وأسبوع
        $endDate = (clone $startDate)->modify('+2 hours'); // تاريخ الانتهاء بعد ساعتين من تاريخ البدء

        return [
            'user_id' => User::factory(), // إنشاء مستخدم جديد افتراضيًا
            'manager_id' => User::factory(), // إنشاء مدير جديد افتراضيًا
            'table_id' => Table::factory(), // إنشاء طاولة جديدة افتراضيًا
            'start_date' => $startDate,
            'end_date' => $endDate,
            'guest_count' => $this->faker->numberBetween(1, 10), // عدد الضيوف بين 1 و 10
            'services' => $this->faker->sentence(), // خدمات عشوائية
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']), // حالة عشوائية
            // 'cancelled_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'), // قد تكون NULL أو تاريخ عشوائي
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
