<?php

namespace Database\Seeders;

use App\Enums\RoleUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create([
            'name' => RoleUser::Admin->value
        ]);

        $customer = Role::create([
            'name' => RoleUser::Customer->value
        ]);

        $reservationManager = Role::create([
            'name' => RoleUser::ReservationManager->value
        ]);

        $waiter = Role::create([
            'name' => RoleUser::Waiter->value
        ]);
    }
}
