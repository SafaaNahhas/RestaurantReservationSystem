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

        // Assign Roles to Reservations Permissions
        $storeReservation->assignRole($customer);
        $updateReservation->assignRole($customer);
        $confirmReservation->assignRole([$admin, $reservationManager]);
        $rejectReservation->assignRole([$admin, $reservationManager]);
        $startService->assignRole([$admin, $reservationManager, $captin]);
        $completeService->assignRole([$admin, $reservationManager, $captin]);
        $hardDeleteReservation->assignRole([$admin, $reservationManager]);
        $cancelUnConfirmed->assignRole($admin);

        // Ratings Permissions
        $deleteRating = Permission::create(['name' => 'delete rating']);

        // Assign Roles to Ratings Permissions
        $deleteRating->assignRole([$admin, $reservationManager]);



        // Tables Permissions
        $storeTable = Permission::create(['name' => 'store table']);
        $updateTable = Permission::create(['name' => 'update table']);
        $softDeleteTable = Permission::create(['name' => 'soft delete table']);
        $forceDeleteTable = Permission::create(['name' => 'force delete table']);
        $restoreTable = Permission::create(['name' => 'restor table']);

        $storeTable->assignRole($admin);
        $updateTable->assignRole($admin);
        $softDeleteTable->assignRole($admin);
        $forceDeleteTable->assignRole($admin);
        $restoreTable->assignRole($admin);


        // Roles Permissions
        $storeRole = Permission::create(['name' => 'store role']);
        $updateRole = Permission::create(['name' => 'update role']);
        $deleteRole = Permission::create(['name' => 'delete role']);

        $storeRole->assignRole($admin);
        $updateRole->assignRole($admin);
        $deleteRole->assignRole($admin);


        // Permissions Permissions
        $storePermission = Permission::create(['name' => 'store permission']);
        $updatePermission = Permission::create(['name' => 'update permission']);
        $deletePermission = Permission::create(['name' => 'delete permission']);

        $storePermission->assignRole($admin);
        $updatePermission->assignRole($admin);
        $deletePermission->assignRole($admin);


        //  ForgetPassword Permissions
        $checkEmail = Permission::create(['name' => 'store permission']);
        $checkCode = Permission::create(['name' => 'update permission']);
        $changePassword = Permission::create(['name' => 'delete permission']);

        $checkEmail->assignRole([$admin, $reservationManager, $customer, $captin]);
        $checkCode->assignRole([$admin, $reservationManager, $customer, $captin]);
        $changePassword->assignRole([$admin, $reservationManager, $customer, $captin]);


        // NotificationSettings  Permissions
        $storeNotificationSettings = Permission::create(['name' => 'store notification settings']);
        $updateNotificationSettings = Permission::create(['name' => 'update notification settings']);
        $checkIfNotificationSettingsExsits = Permission::create(['name' => 'check if notification settings exsits']);
        $resetNotificationSettings = Permission::create(['name' => 'reset notification settings']);

        $storeNotificationSettings->assignRole([$admin, $reservationManager, $customer, $captin]);
        $updateNotificationSettings->assignRole([$admin, $reservationManager, $customer, $captin]);
        $checkIfNotificationSettingsExsits->assignRole([$admin, $reservationManager, $customer, $captin]);
        $resetNotificationSettings->assignRole([$admin, $reservationManager, $customer, $captin]);
    }
}
