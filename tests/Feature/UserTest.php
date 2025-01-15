<?php

namespace Tests\Feature;

use App\Enums\RoleUser;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions; // This will wrap each test in a transaction

    protected mixed $userService;


    protected function startCode(): array
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'm.k@email.com',
            'name' => 'Admin User',
            'is_active' => true
        ]);

        $admin->assignRole(RoleUser::Admin);
        $token = JWTAuth::fromUser($admin);

        return [
            'admin' => $admin,
            'token' => $token
        ];
    }


    /**
     * Test if users endpoint returns correct pagination for admin.
     *
     * @return void
     */
    public function test_list_users_with_pagination(): void
    {
        // Setup authentication and initial data
        $data = $this->startCode();
        $token = $data['token'];

        // Get baseline count before adding test data
        $initialCount = User::count();

        // Test parameters
        $numberOfUsers = 15;  // Total users to create
        $perPage = 10;       // Users per page

        // Create test users
        User::factory()
            ->count($numberOfUsers)
            ->create();

        // Make API request with admin token
        $response = $this->getJson("/api/users?per_page={$perPage}", [
            'Authorization' => 'Bearer ' . $token
        ]);

        // Assert response status
        $response->assertStatus(200);
    }


    /**
     * Test unauthorized access.
     *
     * @return void
     */
    public function test_unauthorized_access_is_rejected(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }


    /**
     * Test successful user creation.
     *
     * @return void
     */
    public function test_admin_can_create_user(): void
    {
        $data = $this->startCode();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'is_active' => true,
            'role' => RoleUser::Customer
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $data['token']
        ]);

        $response->assertStatus(201);
    }


    /**
     * Test user creation with invalid role fails.
     *
     * @return void
     */
    public function test_creates_user_with_invalid_role_fails(): void
    {
        $data = $this->startCode();

        $userData = [
            'name' => 'user',
            'email' => 'user.user@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'is_active' => true,
            'role' => 'invalid_role' // Invalid role value
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $data['token']
        ]);

        // Assert validation failed
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        // Assert user was not created in database
        $this->assertDatabaseMissing('users', [
            'email' => 'john.doe@example.com'
        ]);
    }

    /**
     * Test user creation fails with duplicate email.
     *
     * @return void
     */
    public function test_user_creation_fails_with_duplicate_email(): void
    {
        $data = $this->startCode();

        // Create first user
        $userData = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'is_active' => true,
            'role' => RoleUser::Customer
        ];

        // Create the first user successfully
        $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $data['token']
        ]);

        // Try to create second user with same email
        $duplicateUserData = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com', // Same email as first user
            'password' => 'password456',
            'phone' => '0987654321',
            'is_active' => true,
            'role' => RoleUser::Customer
        ];

        $response = $this->postJson('/api/users', $duplicateUserData, [
            'Authorization' => 'Bearer ' . $data['token']
        ]);

        // Assert validation failed
        $response->assertStatus(422);
    }

    /**
     * Test user can update their own profile.
     *
     * @return void
     */
    public function test_can_update_user_successfully(): void
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '1234567890',
            'password' => 'password123456',
            'is_active' => true
        ]);

        // Login as the same user
        $this->actingAs($user, 'api');
        $token = JWTAuth::fromUser($user);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '0987654321',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        // User attempts to update their own profile
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/users/" . $user->id, $updateData);

        $response->assertStatus(200);
    }


    /**
     * Test email update with validation.
     *
     * @return void
     */
    public function test_cannot_update_with_invalid_email(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/users/{$user->id}", [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    /**
     * Test admin can delete a user.
     *
     * @return void
     */
    public function test_admin_can_delete_user(): void
    {
        $data = $this->startCode();

        // Create a user to be deleted
        $userToDelete = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
        $userToDelete->assignRole(RoleUser::Customer);

        // Admin attempts to delete the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(204);

        // Verify user is soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
            'email' => 'test@example.com'
        ]);
    }


    /**
     * Test non-admin cannot delete a user.
     *
     * @return void
     */
    public function test_non_admin_cannot_delete_user(): void
    {
        // Create a regular user
        $regularUser = User::factory()->create();
        $regularUser->assignRole(RoleUser::Customer);
        $token = JWTAuth::fromUser($regularUser);

        // Create another user to attempt to delete
        $userToDelete = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com'
        ]);
        $userToDelete->assignRole(RoleUser::Customer);

        // Regular user attempts to delete another user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/users/{$userToDelete->id}");

        // Assert the deletion was forbidden
        $response->assertStatus(403);

        // Verify target user still exists
        $this->assertDatabaseHas('users', [
            'id' => $userToDelete->id,
            'email' => 'target@example.com',
            'deleted_at' => null
        ]);
    }


    /**
     * Test deleting non-existent user returns 404.
     *
     * @return void
     */
    public function test_delete_nonexistent_user_returns_404(): void
    {
        $data = $this->startCode();

        $this->withoutExceptionHandling();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Attempt to delete non-existent user
        $nonExistentId = 99999;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->deleteJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404);
    }

    /**
     * Test admin can restore a deleted user.
     *
     * @return void
     */
    public function test_admin_can_restore_deleted_user(): void
    {
        $data = $this->startCode();

        // Create and soft delete a user
        $userToRestore = User::factory()->create([
            'name' => 'Deleted User',
            'email' => 'deleted@example.com',
            'is_active' => true
        ]);
        $userToRestore->assignRole(RoleUser::Manager);
        $userToRestore->delete();

        // Verify user is soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $userToRestore->id,
            'email' => 'deleted@example.com'
        ]);

        // Admin attempts to restore the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->postJson("/api/users/restore/{$userToRestore->id}");

        $response->assertStatus(200);
    }


    /**
     * Test non-admin cannot restore a deleted user.
     *
     * @return void
     */
    public function test_non_admin_cannot_restore_deleted_user(): void
    {
        // Create a regular user
        $regularUser = User::factory()->create();
        $regularUser->assignRole(RoleUser::Manager);
        $token = JWTAuth::fromUser($regularUser);

        // Create and soft delete another user
        $userToRestore = User::factory()->create([
            'name' => 'Deleted User',
            'email' => 'deleted@example.com'
        ]);
        $userToRestore->delete();

        // Regular user attempts to restore the deleted user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/users/restore/{$userToRestore->id}");

        // Assert the restoration was forbidden
        $response->assertStatus(403);

        // Verify user remains deleted
        $this->assertSoftDeleted('users', [
            'id' => $userToRestore->id
        ]);
    }


    /**
     * Test restoring non-existent user returns 404.
     *
     * @return void
     */
    public function test_restore_nonexistent_user_returns_404(): void
    {
        $data = $this->startCode();

        // Attempt to restore non-existent user
        $nonExistentId = 99999;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->postJson("/api/users/restore/{$nonExistentId}");

        $response->assertStatus(404);
    }


    /**
     * Test restoring an active (non-deleted) user.
     *
     * @return void
     */
    public function test_cannot_restore_non_deleted_user(): void
    {
        $data = $this->startCode();

        // Create an active user
        $activeUser = User::factory()->create([
            'name' => 'Active User',
            'email' => 'active@example.com'
        ]);

        // Attempt to restore an active user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->postJson("/api/users/restore/{$activeUser->id}");

        // Should return 404 as user is not in trash
        $response->assertStatus(404);
    }


    /**
     * Test admin can view deleted users.
     *
     * @return void
     */
    public function test_admin_can_view_deleted_users(): void
    {
        $data = $this->startCode();

        // Create and soft delete multiple users
        $deletedUsers = User::factory()->count(3)->create()->each(function ($user) {
            $user->delete();
        });

        // Admin attempts to view deleted users
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->getJson("/api/show-deleted-users");

        $response->assertStatus(200);
    }


    /**
     * Test non-admin cannot view deleted users.
     *
     * @return void
     */
    public function test_non_admin_cannot_view_deleted_users(): void
    {
        // Create and login as regular user
        $regularUser = User::factory()->create();
        $regularUser->assignRole(RoleUser::Manager);
        $token = JWTAuth::fromUser($regularUser);

        // Create and delete some users
        User::factory()->count(3)->create()->each(function ($user) {
            $user->delete();
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/show-deleted-users");

        $response->assertStatus(403);
    }


    /**
     * Test admin can force delete a soft-deleted user
     */
    public function test_admin_can_force_delete_soft_deleted_user(): void
    {
        $data = $this->startCode();

        // Create and soft delete a user
        $userToDelete = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        $userToDelete->assignRole(RoleUser::Manager);
        $userToDelete->delete();

        // Verify user is soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id
        ]);

        // Admin attempts to force delete the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->deleteJson("/api/force-delete/{$userToDelete->id}/");

        $response->assertStatus(200);

        // Verify user is completely removed from database
        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id
        ]);
    }


    /**
     * Test non-admin cannot force delete user.
     *
     * @return void
     */
    public function test_non_admin_cannot_force_delete_user(): void
    {
        // Create a regular user
        $regularUser = User::factory()->create();
        $regularUser->assignRole(RoleUser::Manager);
        $token = JWTAuth::fromUser($regularUser);

        // Create a user to delete
        $userToDelete = User::factory()->create();
        $userToDelete->assignRole(RoleUser::Manager);
        $userToDelete->delete();

        // Regular user attempts to force delete
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/force-delete/{$userToDelete->id}");

        $response->assertStatus(403);

        // Verify user still exists in database
        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id
        ]);
    }


    /**
     * Test force delete non-existent user returns.
     *
     * @return void
     */
    public function test_force_delete_nonexistent_user_returns_404(): void
    {
        $data = $this->startCode();

        // Attempt to force delete non-existent user
        $nonExistentId = 99999;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $data['token']
        ])->deleteJson("/api/force-delete/{$nonExistentId}");

        $response->assertStatus(404);
    }
}
