<?php

namespace Database\Seeders;

use App\Enums\RoleUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //  Main Roles
        $admin = Role::create([
            'name' => RoleUser::Admin->value
        ]);

        $manager = Role::create([
            'name' => RoleUser::Manager->value
        ]);
        $customer = Role::create([
            'name' => RoleUser::Customer->value
        ]);


        $waiter = Role::create([
            'name' => RoleUser::Waiter->value
        ]);

        // Reservation Permissions
        $storeReservation = Permission::create(['name' => 'store reservation']);
        $updateReservation = Permission::create(['name' => 'update reservation']);
        $confirmReservation = Permission::create(['name' => 'confirm reservation']);
        $rejectReservation = Permission::create(['name' => 'reject reservation']);
        $cancelUnConfirmed = Permission::create(['name' => 'cancel unconfirmed reservation']);
        $startService = Permission::create(['name' => 'start service']);
        $completeService = Permission::create(['name' => 'complete service']);
        $hardDeleteReservation = Permission::create(['name' => 'hard delete reservation']);

        // Assign Roles to Reservations Permissions
        $storeReservation->assignRole($customer);
        $updateReservation->assignRole($customer);
        $confirmReservation->assignRole([$admin, $manager]);
        $rejectReservation->assignRole([$admin, $manager]);
        $startService->assignRole([$admin, $manager, $waiter]);
        $completeService->assignRole([$admin, $manager, $waiter]);
        $hardDeleteReservation->assignRole([$admin, $manager]);
        $cancelUnConfirmed->assignRole($admin);

        // Ratings Permissions
        $deleteRating = Permission::create(['name' => 'delete rating']);

        // Assign Roles to Ratings Permissions
        $deleteRating->assignRole([$admin, $manager]);
    }
}
 