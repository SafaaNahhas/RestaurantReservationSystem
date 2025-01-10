<?php

namespace Tests\Feature\Department;

use Tests\TestCase;
use App\Models\User;
use App\Enums\RoleUser;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DepartmentTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $customerUser;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        // Create necessary roles
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);
        Role::firstOrCreate(['name' => RoleUser::Manager->value]);

        // Create users using factories
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(RoleUser::Admin->value);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole(RoleUser::Customer->value);

        $this->manager = User::factory()->create();
        $this->manager->assignRole(RoleUser::Manager->value);
    }

    /** @test Get list of all departments */
    public function it_can_list_all_departments()
    {
        // Create some departments in the database
        Department::factory()->count(3)->create();

        // Get all departments as an admin user
        $response = $this->actingAs($this->adminUser)->get('/api/department');

        // Assert the response status and check if departments are returned
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Departments retrieved successfully.',
        ]);
    }

    /** @test */
public function it_returns_empty_list_when_no_departments_exist()
{
    // Ensure no departments exist
    Department::query()->delete();

    // Get all departments as an admin user
    $response = $this->actingAs($this->adminUser)->get('/api/department');

    // Assert the response status and check for an empty data array
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => 'Departments retrieved successfully.',
        'data' => [], // Ensure data array is empty
    ]);
}

    /** @test Create a department */
    public function test_it_can_create_a_department_with_valid_data()
    {
        // Create a user with the 'Manager' role
        $user = User::factory()->create();
        $user->assignRole('Manager');
    
        // Department data
        $departmentData = [
            'name' => 'Sample Department',
            'description' => 'Sample description for department',
            'manager_id' => $user->id,
        ];
    
        // Attempt to create a department
        $response = $this->actingAs($this->adminUser)->postJson('/api/department', $departmentData);
    
        // Assert the department was created successfully
        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department created successfully.',
        ]);
        $response->assertJsonFragment([
            'name' => $departmentData['name'],
        ]);
    }

    /** @test Fail to create a department with missing required fields */
    public function test_it_fails_to_create_a_department_with_missing_required_fields()
    {
        // Attempt to create a department with missing fields
        $departmentData = ['description'];
    
        // Attempt to create the department
        $response = $this->actingAs($this->adminUser)->postJson('/api/department', $departmentData);
    
        // Assert validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'manager_id']);
    }

    /** @test Fail to create a department with an invalid manager ID */
    public function test_it_fails_to_create_a_department_with_invalid_manager_id()
    {
        // Attempt to create a department with a non-existent manager ID
        $departmentData = [
            'name' => 'Invalid Department',
            'description' => 'Sample description for department',
            'manager_id' => 9999, // Non-existent manager ID
        ];
    
        // Attempt to create the department
        $response = $this->actingAs($this->adminUser)->postJson('/api/department', $departmentData);
    
        // Assert validation error for manager_id
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['manager_id']);
    }
    
    /** @test Fail to create a department with invalid data types */
    public function test_it_fails_to_create_a_department_with_invalid_data_types()
    {
        // Department data with invalid types
        $departmentData = [
            'name' => 12345, // Invalid type
            'description' => ['invalid', 'description'], // Invalid type
            'manager_id' => 'invalid_id', // Invalid type
        ];
    
        // Attempt to create a department with invalid data
        $response = $this->actingAs($this->adminUser)->postJson('/api/department', $departmentData);
    
        // Assert validation error for invalid data types
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'description', 'manager_id']);
    }

    /** @test Show department details */
    public function test_it_can_show_department_details()
    {
        // Create a department
        $department = Department::factory()->create();

        // Fetch the department details by ID
        $response = $this->actingAs($this->adminUser)->getJson("/api/department/{$department->id}");

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department retrieved successfully.',
        ]);
        $response->assertJsonFragment([
            'name' => $department->name,
        ]);
    }


    
    /** @test Update department details */
    public function it_can_update_a_department()
    {
        // Create a department
        $department = Department::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Manager');

        // New department data
        $updatedData = [
            'name' => 'Updated Department Name',
            'description' => 'Updated description for department',
            'user' => $user->id, 
        ];

        // Update the department
        $response = $this->actingAs($this->adminUser)->putJson("/api/department/{$department->id}", $updatedData);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department updated successfully.',
        ]);
        $response->assertJsonFragment([
            'name' => 'Updated Department Name',
            'description' => 'Updated description for department',
        ]);
    }


    /** @test Delete department */
    public function test_it_can_delete_a_department()
    {
        // Create a department
        $department = Department::factory()->create();

        // Delete the department
        $response = $this->actingAs($this->adminUser)->deleteJson("/api/department/{$department->id}");

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department deleted successfully.',
        ]);

        // Assert the department is soft-deleted
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    /** @test Restore department */
    public function it_can_restore_a_deleted_department()
    {
        // Soft delete a department
        $department = Department::factory()->create();
        $department->delete();

        // Restore the department
        $response = $this->actingAs($this->adminUser)->putJson("/api/department/{$department->id}/restore");

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department restored successfully.',
        ]);

        // Assert the department is restored
        $this->assertNotSoftDeleted($department);
    }
/** @test */
    // Test to show all soft-deleted Department
    public function it_can_show_deleted_events()
    {
        // Soft delete an $department
        $department = Department::factory()->create();
        $department->delete();

        // Retrieve soft-deleted Department
        $response = $this->actingAs($this->adminUser)->getJson('/api/department/alldelet');

        // Assert that the deleted depa$department is returned
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Soft-deleted departments retrieved successfully.',
        ]);
        $response->assertJsonFragment([
            'id' => $department->id,
        ]);
    }

    /** @test Permanently delete a department */
    public function it_can_permanently_delete_a_deleted_department()
    {
        // Soft delete a department
        $department = Department::factory()->create();
        $department->delete();

        // Permanently delete the department
        $response = $this->actingAs($this->adminUser)->deleteJson("/api/department/{$department->id}/delete");

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Department permanently deleted.',
        ]);

        // Assert the department is permanently deleted
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
