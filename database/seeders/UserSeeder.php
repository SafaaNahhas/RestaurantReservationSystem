<?php

namespace Database\Seeders;

use App\Enums\RoleUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'name'     => 'new admin',
            'email'    => 'newAdmin@example.com',
            'password' => Hash::make('123456789')
        ]);

        $user->assignRole(RoleUser::Admin->value);


        $manager1=User::create([
            'name'     => 'manager11',
            'email'    => 'manager11@example.com',
            'password' => Hash::make('123456789')
        // $user1 = User::create([
        //         'name'     => 'mohammed',
        //     'email'    => 'mohammedalmostfa36@gmail.com',
        //     'password' => Hash::make('12345678'),
        ]);

        $manager1->assignRole(RoleUser::ReservationManager->value);

        $manager2=User::create([
            'name'     => 'manager22',
            'email'    => 'manager22@example.com',
            'password' => Hash::make('123456789')
        ]);

        $manager2->assignRole(RoleUser::ReservationManager->value);



    }
}
