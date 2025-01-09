<?php

namespace Database\Seeders;

use App\Enums\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'new admin',
                'email' => 'newAdmin@example.com',
                'password' => '123456789',
                'role' => RoleUser::Admin->value,
            ],
            [
                'name' => 'manager11',
                'email' => 'manager11@example.com',
                'password' => '123456789',
                'role' => RoleUser::ReservationManager->value,
            ],
            [
                'name' => 'manager2',
                'email' => 'manager2@example.com',
                'password' => '123456789',
                'role' => RoleUser::ReservationManager->value,
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@example.com',
                'password' => '123456789',
                'role' => RoleUser::Customer->value,
            ],
            [
                'name' => 'Customer2',
                'email' => 'customer2@example.com',
                'password' => '123456789',
                'role' => RoleUser::Customer->value,
            ],
            [
                'name' => 'captin2',
                'email' => 'captin2@example.com',
                'password' => '123456789',
                'role' => RoleUser::Captin->value,
            ],
            [
                'name' => 'captin21',
                'email' => 'captin21@example.com',
                'password' => '123456789',
                'role' => RoleUser::Captin->value,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            $user->assignRole($userData['role']);

            $this->command->info("User '{$userData['email']}' created or already exists.");
        }
    }
}
