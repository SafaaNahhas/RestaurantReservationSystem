<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Enums\RoleUser;
use App\Models\Department;
use App\Models\Reservation;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{
    use DatabaseTransactions;
    protected $adminUser;
    protected $customerUser;
    protected $managerUser;
    protected $department;
    protected $table;
    protected $token;
    protected $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary roles and permissions
        $this->createRolesAndPermissions();

        // Create admin, customer, and manager users
        $this->adminUser = $this->createUserWithRole(RoleUser::Admin);
        $this->customerUser = $this->createUserWithRole(RoleUser::Customer);
        $this->managerUser = $this->createUserWithRole(RoleUser::Manager);

        // Ensure the customer user has the 'store reservation' permission
        $this->customerUser->givePermissionTo('store reservation');
        $this->customerUser->givePermissionTo('update reservation');

        // Create a department and table for reservations
        $this->department = $this->createDepartment($this->managerUser);
        $this->table = $this->createTable($this->department);

        // Generate JWT token for customer user
        $this->token = JWTAuth::fromUser($this->customerUser);

        // Add notification settings for the customer
        $this->customerUser->notificationSettings()->create([
            'reservation_send_notification' => ["confirm"],
            'method_send_notification' => 'mail',
        ]);

        // Create an initial reservation for testing update
        $this->reservation = Reservation::create([
            'user_id' => $this->customerUser->id,
            'table_id' => $this->table->id,
            'guest_count' => 4,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'pending',
            'notification_method' => false,
        ]);
    }

    public function createRolesAndPermissions(): void
    {
        // Ensure roles exist
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);
        Role::firstOrCreate(['name' => 'Reservation Manager']);

        // Ensure the 'store reservation' permission exists
        Permission::firstOrCreate(['name' => 'store reservation', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'update reservation', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'getAllTablesWithReservations', 'guard_name' => 'api']);
    }

    public function createUserWithRole($role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);
        return $user;
    }

    public function createDepartment($manager): Department
    {
        return Department::create([
            'name' => 'Dining',
            'description' => 'Responsible for dining reservations.',
            'manager_id' => $manager->id,
        ]);
    }

    public function createTable($department): Table
    {
        return Table::factory()->create([
            'seat_count' => 6,
            'department_id' => $department->id,
            'table_number' => 'T' . uniqid(),
        ]);
    }

    public function test_it_creates_a_reservation_successfully()
    {
        $reservationData = [
            'table_id' => $this->table->id,
            'guest_count' => 4,
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'services' => 'service1', // Optional services
            'notification_method' => false,
        ];

        // Make the request to create a reservation with the token
        $response = $this->postJson(
            'api/reservations',
            $reservationData,
            [
                'Authorization' => 'Bearer ' . $this->token, // Include the token for authorization
            ]
        );

        // Dump the response to see the error message and details
        $response->dump();

        // Ensure the reservation was created successfully
        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->customerUser->id,
            'guest_count' => $reservationData['guest_count'],
            'status' => 'pending',
        ]);
    }
}
