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
        $softDeleteReservation = Permission::create(['name' => 'soft delete reservation']);
        $restoreReservation = Permission::create(['name' => 'restorereservation']);
        $viewSoftDeletedReservations = Permission::create(['name' => 'view soft delete reservation']);
        // $getAllTablesWithReservations = Permission::create(['name' => 'getAllTablesWithReservations']);
        $viewMostFrequentUser = Permission::create(['name' => 'viewMostFrequentUser']);
        $viewReservationsByManager = Permission::create(['name' => 'viewReservationsByManager']);

        // Assign Roles to Reservations Permissions
        $storeReservation->assignRole($customer);
        $updateReservation->assignRole($customer);
        $confirmReservation->assignRole([$admin, $manager]);
        $rejectReservation->assignRole([$admin, $manager]);
        $startService->assignRole([$admin, $manager, $waiter]);
        $completeService->assignRole([$admin, $manager, $waiter]);
        $hardDeleteReservation->assignRole([$admin, $manager]);
        $softDeleteReservation->assignRole([$admin, $manager]);
        $restoreReservation->assignRole([$admin, $manager]);
        $viewSoftDeletedReservations->assignRole([$admin, $manager]);

        // $getAllTablesWithReservations->assignRole($admin);
        $viewMostFrequentUser->assignRole($admin);
        $viewReservationsByManager->assignRole([$admin, $manager]);

        // Ratings Permissions
        $deleteRating = Permission::create(['name' => 'delete rating']);

        // Assign Roles to Ratings Permissions
        $deleteRating->assignRole([$admin, $manager]);


        $deleteRating->assignRole([$admin, $manager]);


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
        $assignPermissioToRole = Permission::create(['name' => 'assign permissio to role']);
        $removePermissioFromRole = Permission::create(['name' => 'remove permissio from role']);

        $storeRole->assignRole($admin);
        $updateRole->assignRole($admin);
        $deleteRole->assignRole($admin);
        $assignPermissioToRole->assignRole($admin);
        $removePermissioFromRole->assignRole($admin);


        // Permissions Permissions
        $storePermission = Permission::create(['name' => 'store permission']);
        $updatePermission = Permission::create(['name' => 'update permission']);
        $deletePermission = Permission::create(['name' => 'delete permission']);

        $storePermission->assignRole($admin);
        $updatePermission->assignRole($admin);
        $deletePermission->assignRole($admin);


        //  ForgetPassword Permissions
        $checkEmail = Permission::create(['name' => 'check email']);
        $checkCode = Permission::create(['name' => 'check code']);
        $changePassword = Permission::create(['name' => 'change password']);

        $checkEmail->assignRole([$admin, $manager, $customer, $waiter]);
        $checkCode->assignRole([$admin, $manager, $customer, $waiter]);
        $changePassword->assignRole([$admin, $manager, $customer, $waiter]);


        // NotificationSettings  Permissions
        $storeNotificationSettings = Permission::create(['name' => 'store notification settings']);
        $updateNotificationSettings = Permission::create(['name' => 'update notification settings']);
        $checkIfNotificationSettingsExsits = Permission::create(['name' => 'check if notification settings exsits']);
        $resetNotificationSettings = Permission::create(['name' => 'reset notification settings']);

        $storeNotificationSettings->assignRole([$admin, $manager, $customer, $waiter]);
        $updateNotificationSettings->assignRole([$admin, $manager, $customer, $waiter]);
        $checkIfNotificationSettingsExsits->assignRole([$admin, $manager, $customer, $waiter]);
        $resetNotificationSettings->assignRole([$admin, $manager, $customer, $waiter]);

    }

}
