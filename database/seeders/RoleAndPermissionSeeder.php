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
        $updateReservation = Permission::create(['name' => 'update reservation']);
        $confirmReservation = Permission::create(['name' => 'confirm reservation']);
        $rejectReservation = Permission::create(['name' => 'reject reservation']);
        $cancelUnConfirmed = Permission::create(['name' => 'cancel unconfirmed reservation']);
        $startService = Permission::create(['name' => 'start service']);
        $completeService = Permission::create(['name' => 'complete service']);
        $hardDeleteReservation = Permission::create(['name' => 'hard delete reservation']);
        $softDeleteReservation = Permission::create(['name' => 'soft delete reservation']);
        $restoreReservation = Permission::create(['name' => 'restorereservation']);
        $viewSoftDeletedReservations = Permission::create(['name' => 'view soft delete reservation']);
        // $getAllTablesWithReservations = Permission::create(['name' => 'getAllTablesWithReservations']);
        $viewMostFrequentUser = Permission::create(['name' => 'viewMostFrequentUser']);
        $viewReservationsByManager = Permission::create(['name' => 'viewReservationsByManager']);

        // Assign Roles to Reservations Permissions
        $storeReservation->assignRole($customer);
        $updateReservation->assignRole($customer);
        $confirmReservation->assignRole([$admin, $reservationManager]);
        $rejectReservation->assignRole([$admin, $reservationManager]);
        $startService->assignRole([$admin, $reservationManager, $captin]);
        $completeService->assignRole([$admin, $reservationManager, $captin]);
        $hardDeleteReservation->assignRole([$admin, $reservationManager]);
        $softDeleteReservation->assignRole([$admin, $reservationManager]);
        $restoreReservation->assignRole([$admin, $reservationManager]);
        $viewSoftDeletedReservations->assignRole([$admin, $reservationManager]);
        $cancelUnConfirmed->assignRole($admin);
        // $getAllTablesWithReservations->assignRole($admin);
        $viewMostFrequentUser->assignRole($admin);
        $viewReservationsByManager->assignRole([$admin, $reservationManager]);

        // Ratings Permissions
        $deleteRating = Permission::create(['name' => 'delete rating']);

        // Assign Roles to Ratings Permissions
        $deleteRating->assignRole([$admin, $reservationManager]);
    }
}
