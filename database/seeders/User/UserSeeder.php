<?php

namespace Database\Seeders\User;

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
                'name' => 'Admin',
                'email' => 'Admin@example.com',
                'password' => '123456789',
                'role' => RoleUser::Admin->value,
            ],
            [
                'name' => 'manager1',
                'email' => 'manager1@example.com',
                'password' => '123456789',
                'role' => RoleUser::Manager->value,
            ],
            [
                'name' => 'manager2',
                'email' => 'manager2@example.com',
                'password' => '123456789',
                'role' => RoleUser::Manager->value,
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
                'name' => 'Waiter1',
                'email' => 'Waiter1@example.com',
                'password' => '123456789',
                'role' => RoleUser::Waiter->value,
            ],
            [
                'name' => 'Waiter2',
                'email' => 'Waiter2@example.com',
                'password' => '123456789',
                'role' => RoleUser::Waiter->value,
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
