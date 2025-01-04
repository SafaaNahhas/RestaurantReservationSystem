<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions; // This will wrap each test in a transaction
    use WithoutMiddleware; // Optional: if you want to skip middleware

    protected mixed $userService;

    /**
     *  Set up the test environment.
     *  Creates a new instance of UserService before each test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userService = app(\App\Services\UserService::class);
    }

    /**
     * Test if user listing returns correct pagination
     *
     * @return void
     */
    public function test_list_users_returns_paginated_results(): void
    {
        // Get baseline count before adding test data
        $initialCount = User::count();

        // Test parameters
        $numberOfUsers = 15;  // Total users to create
        $perPage = 10;        // Users per page

        // Seed test data
        User::factory()->count($numberOfUsers)->create();

        $result = $this->userService->listUsers($perPage);

        // Verify pagination works correctly
        $this->assertEquals($perPage, $result->perPage());                                   // Correct items per page
        $this->assertEquals($initialCount + $numberOfUsers, $result->total());      // Correct total count
        $this->assertEquals(ceil(($initialCount + $numberOfUsers) / $perPage), $result->lastPage());     // Correct number of pages
        $this->assertEquals(min($perPage, $result->total()), $result->count());               // Correct items on current page
    }

    /**
     * Test if users are ordered by latest first
     *
     * @return void
     */
    public function test_list_users_orders_by_latest(): void
    {
        // Create an older user
        User::factory()->create(['created_at' => now()->subDays(2)]);

        // Create the most recent user
        $latestUser = User::factory()->create(['created_at' => now()]);

        // Get paginated results
        $result = $this->userService->listUsers(10);

        // Verify the most recent user appears first
        $this->assertEquals($latestUser->id, $result->first()->id);
    }

    /**
     * Test user creation fails with invalid role
     *
     * @return void
     */
    public function test_user_creation_fails_with_invalid_role(): void
    {
        $invalidRoleData = [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'InvalidRole' // Role that doesn't exist
        ];

        $this->expectException(RoleDoesNotExist::class);
        $this->userService->createUser($invalidRoleData);
    }

    /**
     * Test successful user creation with role assignment
     *
     * @return void
     */
    public function test_creates_user_with_role_successfully(): void
    {
        // Prepare test data with user details
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'Customer'
        ];

        // Execute user creation through service
        $user = $this->userService->createUser($userData);

        // Verify user was saved to database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Verify password was properly hashed
        $this->assertTrue(Hash::check('password123', $user->password));

        // Verify role was assigned correctly
        $this->assertTrue($user->hasRole('Customer'));
    }

    /**
     * Test user creation fails with duplicate email
     *
     * @return void
     */
    public function test_user_creation_fails_with_duplicate_email(): void
    {
        // Create initial user
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // Attempt to create user with duplicate email
        $duplicateEmailData = [
            'name' => 'Test User',
            'email' => 'test@example.com', // Duplicate email
            'password' => 'password123',
            'role' => 'Customer'
        ];

        $this->expectException(UniqueConstraintViolationException::class);
        $this->userService->createUser($duplicateEmailData);
    }

    /**
     * Test user update with various field combinations
     *
     * @return void
     */
    public function test_updates_user_successfully(): void
    {
        // Create initial user with known password hash
        $originalPassword = Hash::make('originalpass');
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => $originalPassword
        ]);

        // Prepare update data including null value to test filtering
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'new@example.com',
            'password' => 'newpassword',
            'phone' => null
        ];

        // Execute update operation
        $updatedUser = $this->userService->updateUser($user, $updateData);

        // Verify basic field updates
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);

        // Verify password handling
        $this->assertTrue(Hash::check('newpassword', $updatedUser->password));   // New password works
        $this->assertNotEquals($originalPassword, $updatedUser->password);             // Old password hash changed

        // Confirm database persistence
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'new@example.com'
        ]);

        // Verify null value handling
        $this->assertArrayNotHasKey('phone', $updatedUser->getChanges());
    }

    /**
     * Test user update fails with duplicate email
     *
     * @return void
     */
    public function test_update_user_fails_with_duplicate_email(): void
    {
        // Create two users
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userToUpdate = User::factory()->create([
            'email' => 'original@example.com'
        ]);

        // Attempt to update with existing email
        $updateData = [
            'name' => 'New Name',
            'email' => 'existing@example.com' // Duplicate email
        ];

        $this->expectException(UniqueConstraintViolationException::class);
        $this->userService->updateUser($userToUpdate, $updateData);
    }

    /**
     * Test user update fails when user not found
     *
     * @return void
     */
    public function test_update_user_fails_when_user_not_found(): void
    {
        $updateData = [
            'name' => 'New Name',
            'email' => 'new@example.com'
        ];

        try {
            $nonExistentUser = User::findOrFail(999);
            $this->userService->updateUser($nonExistentUser, $updateData);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true); // Test passes if exception is caught
            return;
        }

        $this->fail('Expected ModelNotFoundException was not thrown');
    }

    /**
     * Test successful user deletion
     *
     * @return void
     */
    public function test_deletes_user_successfully(): void
    {
        // Create a user to delete
        $user = User::factory()->create([
            'email' => 'delete-test@example.com'
        ]);
        $userId = $user->id;

        // Delete the user
        $this->userService->deleteUser($user);

        // Assert user was soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $userId,
            'email' => 'delete-test@example.com'
        ]);

        // Verify the record still exists but is soft deleted
        $deletedUser = User::withTrashed()->find($userId);
        $this->assertNotNull($deletedUser->deleted_at);
    }

    /**
     * Test user deletion fails when user not found
     *
     * @return void
     */
    public function test_delete_user_fails_when_user_not_found(): void
    {
        // Create and then delete a user
        $user = User::factory()->create([
            'email' => 'test-delete@example.com'
        ]);

        $user->forceDelete();

        $this->expectException(ModelNotFoundException::class);

        $deletedUser = User::findOrFail($user->id);
        $this->userService->deleteUser($deletedUser);
    }

    /**
     * Test user restoration from soft delete state.
     *
     * @return void
     */
    public function test_restores_soft_deleted_user(): void
    {
        // Create test user and soft delete it
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        $user->delete();

        // Confirm user is actually soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);

        // Perform restoration through service
        $restoredUser = $this->userService->restoreUser($user->id);

        // Verify user exists in database without soft delete
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'test@example.com',
            'deleted_at' => null
        ]);

        // Verify restored user data integrity
        $this->assertNull($restoredUser->deleted_at);               // No soft delete timestamp
        $this->assertEquals($user->email, $restoredUser->email);    // Email preserved
        $this->assertEquals($user->name, $restoredUser->name);      // Name preserved
    }

    /**
     * Test user restoration fails when user not soft deleted
     *
     * @return void
     */
    public function test_restore_user_fails_when_user_not_soft_deleted(): void
    {
        // Create an active user (not soft deleted)
        $user = User::factory()->create([
            'email' => 'active@example.com'
        ]);

        try {
            $this->userService->restoreUser($user->id);
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $responseData = json_decode($response->getContent(), true);

            $this->assertEquals(404, $response->getStatusCode());
            $this->assertEquals('error', $responseData['status']);
            $this->assertEquals('User not found  failed!', $responseData['message']);
            $this->assertNull($responseData['data']);
        }
    }


    /**
     * Test retrieval of soft deleted users with pagination
     *
     * @return void
     */
    public function test_show_deleted_users(): void
    {
        // Setup test data - one active and three soft deleted users
        $activeUser = User::factory()->create(['name' => 'Active User']);

        $deletedUsers = User::factory()->count(3)->create()->each(function ($user) {
            $user->delete();
        });

        // Get paginated list of deleted users (2 per page)
        $result = $this->userService->showDeletedUsers(2);

        // Verify pagination settings and counts
        $this->assertEquals(2, $result->perPage());         // Items per page
        $this->assertEquals(3, $result->total());           // Total deleted users
        $this->assertEquals(2, $result->count());           // Current page count
        $this->assertEquals(2, ceil($result->total() / $result->perPage()));     // Total pages

        // Ensure active users are excluded
        $this->assertNotContains($activeUser->id, $result->pluck('id'));

        // Validate returned data structure
        $firstUser = $result->first();
        $this->assertNotNull($firstUser->deleted_at);       // Has deletion timestamp

        // Check all required fields are present
        $this->assertArrayHasKey('id', $firstUser->toArray());
        $this->assertArrayHasKey('name', $firstUser->toArray());
        $this->assertArrayHasKey('email', $firstUser->toArray());
        $this->assertArrayHasKey('phone', $firstUser->toArray());
        $this->assertArrayHasKey('deleted_at', $firstUser->toArray());

        // Confirm records are ordered by latest deleted first
        $this->assertTrue(
            $result->first()->deleted_at->gte($result->last()->deleted_at)
        );
    }

    /**
     * Test retrieval of soft deleted users when none exist
     *
     * @return void
     */
    public function test_show_deleted_users_when_none_exist(): void
    {
        // Create only active users
        $activeUsers = User::factory()->count(3)->create();

        try {
            // Get paginated list of deleted users
            $result = $this->userService->showDeletedUsers(10);
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $responseData = json_decode($response->getContent(), true);

            $this->assertEquals(404, $response->getStatusCode());
            $this->assertEquals('error', $responseData['status']);
            $this->assertEquals('No deleted users found.  failed!', $responseData['message']);
            $this->assertNull($responseData['data']);
        }
    }

    /**
     * Test permanent user deletion including soft deleted records
     *
     * @return void
     */
    public function test_force_delete_user(): void
    {
        // Create test user and soft delete it first
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        $user->delete();

        // Confirm user exists in soft deleted state
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);

        // Execute permanent deletion
        $result = $this->userService->forceDeleteUser($user->id);

        // Verify operation returned success
        $this->assertTrue($result);

        // Confirm user no longer exists in main table
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);

        // Double check user is gone even from soft deletes
        $this->assertNull(User::withTrashed()->find($user->id));
    }

    /**
     * Test force delete fails when user not found
     *
     * @return void
     */
    public function test_force_delete_user_fails_when_user_not_found(): void
    {
        try {
            // Try to delete non-existent user
            $this->userService->forceDeleteUser(999);
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $responseData = json_decode($response->getContent(), true);

            $this->assertEquals(404, $response->getStatusCode());
            $this->assertEquals('error', $responseData['status']);
            $this->assertEquals('User not found.  failed!', $responseData['message']);
            $this->assertNull($responseData['data']);
        }
    }

    /**
     * Test force delete user that was already permanently deleted
     *
     * @return void
     */
    public function test_force_delete_already_permanently_deleted_user(): void
    {
        // Create and permanently delete a user
        $user = User::factory()->create();
        $userId = $user->id;
        $user->forceDelete();

        try {
            // Try to force delete again
            $this->userService->forceDeleteUser($userId);
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $responseData = json_decode($response->getContent(), true);

            $this->assertEquals(404, $response->getStatusCode());
            $this->assertEquals('error', $responseData['status']);
            $this->assertEquals('User not found.  failed!', $responseData['message']);
            $this->assertNull($responseData['data']);
        }
    }
}
