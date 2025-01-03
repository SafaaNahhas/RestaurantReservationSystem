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
            'name'     => 'theadmin',
            'email'    => 'theadmin@example.com',
            'password' => Hash::make('123456789')
        ]);

        $user->assignRole(RoleUser::Admin->value);


        $manager1=User::create([
            'name'     => 'manager1',
            'email'    => 'manager1@example.com',
            'password' => Hash::make('123456789')
        ]);

        $manager1->assignRole(RoleUser::ReservationManager->value);

        $manager2=User::create([
            'name'     => 'manager2',
            'email'    => 'manager2@example.com',
            'password' => Hash::make('123456789')
        ]);

        $manager2->assignRole(RoleUser::ReservationManager->value);



    }
}
