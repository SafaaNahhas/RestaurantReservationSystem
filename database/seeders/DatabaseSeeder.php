<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class
        ]);
        // تشغيل Seeder الخاص بالحجوزات
        \App\Models\Reservation::factory()->count(10)->create();
        \App\Models\Table::factory()->count(10)->create();
    }


}
