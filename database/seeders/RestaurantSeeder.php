<?php

namespace Database\Seeders;

use App\Models\Email;
use App\Models\PhoneNumber;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Corrected the typo 'creat' to 'create' and updated 'website' to a URL
        $restaurant = Restaurant::create([
            'name' => "Alsaadde_Restaurant",
            'location' => "Lattacia",
            'opening_hours' => "9:00 AM",
            'closing_hours' => "11:00 PM",
            'rating' => 5,
            'website' => "https://www.alsaadderestaurant.com",
            'description' => "Alsaadde Restaurant - Best Food in Town",
        ]);

        // Corrected typo 'creat' to 'create' and ensured proper foreign key reference
        PhoneNumber::create([
            'PhoneNumber' => "0991851269",
            'description' => "Restaurant Manager Phone Number",
            'restaurant_id' => $restaurant->id,
        ]);


        Email::create([  // Corrected 'creat' to 'create'
            'email' => "mohammedalmosaytf@gmail.com",
            'description' => "Restaurant Manager Email",
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
