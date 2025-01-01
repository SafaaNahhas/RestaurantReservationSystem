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

        $customer = Role::create([
            'name' => RoleUser::Customer->value
        ]);

        $reservationManager = Role::create([
            'name' => RoleUser::ReservationManager->value
        ]);

        $captin = Role::create([
            'name' => RoleUser::Captin->value
        ]);

        // Reservation Permissions
        $storeReservation = Permission::create(['name' => 'store reservation']);
        $confirmReservation = Permission::create(['name' => 'confirm reservation']);
        $cancelUnConfirmed = Permission::create(['name' => 'cancel unconfirmed reservation']);
        $startService = Permission::create(['name' => 'start service']);
        $completeService = Permission::create(['name' => 'complete service']);
        $hardDeleteReservation = Permission::create(['name' => 'hard delete reservation']);

        // Assign Roles to Reservations Permissions
        $storeReservation->assignRole($customer);
        $confirmReservation->assignRole([$admin, $reservationManager]);
        $startService->assignRole([$admin, $reservationManager, $captin]);
        $completeService->assignRole([$admin, $reservationManager, $captin]);
        $hardDeleteReservation->assignRole([$admin, $reservationManager]);
        $cancelUnConfirmed->assignRole($admin);

        // Ratings Permissions      
        $deleteRating = Permission::create(['name' => 'delete rating']);

        // Assign Roles to Ratings Permissions
        $deleteRating->assignRole([$admin, $reservationManager]);
    }
}
 