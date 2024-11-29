<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // تشغيل Seeder الخاص بالحجوزات
        \App\Models\Reservation::factory()->count(10)->create();
        \App\Models\Table::factory()->count(10)->create();
    }
    
}
