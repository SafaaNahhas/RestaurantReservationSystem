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
        $user = User::create([
            'name'     => 'admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('123456789')
        ]);
        $user->assignRole(RoleUser::Admin->value);
    }
}
