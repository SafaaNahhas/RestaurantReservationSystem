<?php

namespace App\Services;

use App\Enums\RoleUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class UserService extends Controller
{
    /**
     * Retrieves a paginated list of users sorted by most recent first.
     *
     * @param int $perPage Number of users per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws HttpResponseException If retrieval fails
     */
    public function listUsers(int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            // Fetch paginated users ordered by latest
            return User::query()
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            // Handle and log any database or query errors
            Log::error('Error listing users: ' . $e->getMessage());
            throw new HttpResponseException(
                self::error(null, 'Failed to retrieve users list', 500)
            );
        }
    }

    /**
     * Create a new user with hashed password
     *
     * @param array $data User details including password
     * @return User Newly created user instance
     * @throws HttpResponseException If creation fails
     */
    public function createUser(array $data): User
    {
        try {
            // Hash the password before creating user
            $data['password'] = Hash::make($data['password']);

            $user= User::create($data)->fresh();
            $user->assignRole($data['role']);
            return $user;

        } catch (Exception $e) {
            // Log error without exposing sensitive data
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'data' => Arr::except($data, ['password'])
            ]);

            throw new HttpResponseException(
                self::error(null, 'Failed to create user', 500)
            );
        }
    }

    /**
     * Retrieve a specific user
     *
     * @param User $user User model instance
     * @return User
     * @throws HttpResponseException If retrieval fails
     */
    public function getUser(User $user): User
    {
        try {
            return $user;
        } catch (Exception $e) {
            // Log failure with user identifier
            Log::error('Failed to retrieve user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                self::error(null, 'Failed to retrieve user details', 500)
            );
        }
    }

    /**
     * Update user details with optional password change
     *
     * @param User $user User model instance
     * @param array $data Updated user data
     * @return User Updated user instance
     * @throws HttpResponseException If update fails
     */
    public function updateUser(User $user, array $data): User
    {
        try {
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Remove null values and update user
            $filteredData = array_filter($data, static fn($value) => !is_null($value));
            $user->update($filteredData);

            // Log successful update
            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($filteredData)
            ]);

            // Reload fresh user data from database
            return $user->fresh();
        } catch (Exception $e) {
            // Log error without sensitive data
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'data' => Arr::except($data, ['password'])
            ]);

            throw new HttpResponseException(
                self::error(null, 'Failed to update user details', 500)
            );
        }
    }

    /**
     * Delete a user from the system
     *
     * @param User $user User to be deleted
     * @throws HttpResponseException If deletion fails
     * @return void
     */
    public function deleteUser(User $user): void
    {
        try {
            $user->delete();

            // Log deletion for audit trail
            Log::info('User deleted successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
        } catch (Exception $e) {
            // Log failure details
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                self::error(null, 'Failed to delete user', 500)
            );
        }
    }

    /**
     * Restore a soft-deleted user
     *
     * @param int $id ID of deleted user
     * @return User Fresh instance of restored user
     * @throws HttpResponseException If user not found or restoration fails
     */
    public function restoreUser(int $id): User
    {
        try {
            // Find the soft-deleted user
            $user = User::onlyTrashed()->findOrFail($id);

            // Attempt to restore the user
            if (!$user->restore()) {
                throw new Exception('Failed to restore user');
            }

            // Log successful restoration
            Log::info('User restored successfully', [
                'user_id' => $id,
                'user_email' => $user->email
            ]);

            // Get fresh instance of restored user
            return $user->fresh();
        } catch (ModelNotFoundException $e) {
            // Handle case when user not found
            Log::error('User not found for restoration', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new HttpResponseException(
                self::error(null, 'User not found', 404)
            );
        } catch (Exception $e) {
            // Handle general restoration failures
            Log::error('Failed to restore user', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new HttpResponseException(
                self::error(null, 'Failed to restore user', 500)
            );
        }
    }

    /**
     * Get paginated list of soft-deleted users
     *
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated deleted users
     * @throws HttpResponseException If no users found or retrieval fails
     */
    public function showDeletedUsers(int $perPage): LengthAwarePaginator
    {
        try {
            // Get deleted users with specific fields
            $deletedUsers = User::onlyTrashed()
                ->latest()
                ->select(['id', 'name', 'email', 'phone', 'deleted_at'])
                ->paginate($perPage);

            // Check if any deleted users exist
            if ($deletedUsers->isEmpty()) {
                throw new HttpResponseException(
                    self::error(null, 'No deleted users found.', 404)
                );
            }

            return $deletedUsers;
        } catch (Exception $e) {
            // Log retrieval error with details
            Log::error('Error retrieving deleted users:', [
                'per_page' => $perPage,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()  // Provides error stack trace for debugging
            ]);

            throw new HttpResponseException(
                self::error(null, 'An error occurred while retrieving deleted users.', 403)
            );
        }
    }

    /**
     * Permanently delete a user from the database
     *
     * @param int $id User ID to permanently delete
     * @return bool True if deletion successful
     * @throws HttpResponseException If user not found or deletion fails
     */
    public function forceDeleteUser(int $id): bool
    {
        try {
            // Find user including soft-deleted records
            $user = User::withTrashed()->findOrFail($id);

            // Store user details for logging
            $userDetails = [
                'id' => $user->id,
                'email' => $user->email
            ];

            // Permanently remove user from database
            $user->forceDelete();

            // Log successful permanent deletion
            Log::info('User permanently deleted:', $userDetails);
            return true;
        } catch (ModelNotFoundException $e) {
            // Handle case when user not found
            Log::error('Error force deleting user - user not found:', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                self::error(null, 'User not found.', 404)
            );
        } catch (Exception $e) {
            // Handle general deletion failures
            Log::error('Error force deleting user:', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                self::error(null, 'An error occurred while permanently deleting user.', 403)
            );
        }
    }
}
