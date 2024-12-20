<?php

namespace App\Services;

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
     * List users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listUsers(int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            return User::query()
                ->latest()
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error listing users: ' . $e->getMessage());
            throw new HttpResponseException(
                $this->error('Failed to retrieve users list', 500, null)
            );
        }
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     * @throws HttpResponseException
     */
    public function createUser(array $data): User
    {
        try {
            $data['password'] = Hash::make($data['password']);

            return User::create($data)->fresh();
        } catch (Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'data' => Arr::except($data, ['password'])
            ]);

            throw new HttpResponseException(
                $this->error('Failed to create user', 500, null)
            );
        }
    }

    /**
     * Get user by ID
     *
     * @param User $user
     * @return User
     * @throws HttpResponseException
     */
    public function getUser(User $user): User
    {
        try {
            return $user;
        } catch (Exception $e) {
            Log::error('Failed to retrieve user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                $this->error('Failed to retrieve user details', 500, null)
            );
        }
    }

    /**
     * Update user details
     *
     * @param User $user
     * @param array $data
     * @return User
     * @throws HttpResponseException
     */
    public function updateUser(User $user, array $data): User
    {
        try {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Filter out null/empty values and update
            $filteredData = array_filter($data, static fn($value) => !is_null($value));
            $user->update($filteredData);

            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($filteredData)
            ]);

            return $user->fresh();
        } catch (Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'data' => Arr::except($data, ['password'])
            ]);

            throw new HttpResponseException(
                $this->error('Failed to update user details', 500, null)
            );
        }
    }

    /**
     * Delete a user from the system
     *
     * @param User $user
     * @throws HttpResponseException
     * @return void
     */
    public function deleteUser(User $user): void
    {
        try {
            $user->delete();

            Log::info('User deleted successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                $this->error('Failed to delete user', 500, null)
            );
        }
    }

    /**
     * Restore a soft-deleted user
     *
     * @param int $id
     * @return User
     * @throws HttpResponseException
     */
    public function restoreUser(int $id): User
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            if (!$user->restore()) {
                throw new Exception('Failed to restore user');
            }

            Log::info('User restored successfully', [
                'user_id' => $id,
                'user_email' => $user->email
            ]);

            return $user->fresh();
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for restoration', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new HttpResponseException(
                $this->error('User not found', 404, null)
            );
        } catch (Exception $e) {
            Log::error('Failed to restore user', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw new HttpResponseException(
                $this->error('Failed to restore user', 500, null)
            );
        }
    }

    /**
     * Retrieve paginated list of soft-deleted users
     *
     * @param int $perPage Number of items per page
     * @throws HttpResponseException If retrieval fails
     * @return LengthAwarePaginator
     */
    public function showDeletedUsers(int $perPage): LengthAwarePaginator
    {
        try {
            $deletedUsers = User::onlyTrashed()
                ->latest()
                ->select(['id', 'name', 'email', 'phone', 'deleted_at'])
                ->paginate($perPage);

            if ($deletedUsers->isEmpty()) {
                throw new HttpResponseException(
                    $this->error('No deleted users found.', 404, null)
                );
            }

            return $deletedUsers;
        } catch (Exception $e) {
            Log::error('Error retrieving deleted users:', [
                'per_page' => $perPage,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException(
                $this->error('An error occurred while retrieving deleted users.', 403, null)
            );
        }
    }

    /**
     * Permanently delete a user from the database
     *
     * @param $id
     * @return bool
     */
    public function forceDeleteUser($id): bool
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            $userDetails = [
                'id' => $user->id,
                'email' => $user->email
            ];

            $user->forceDelete();

            Log::info('User permanently deleted:', $userDetails);
            return true;
        } catch (ModelNotFoundException $e) {
            Log::error('Error force deleting user - user not found:', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                $this->error('User not found.', 404, null)
            );
        } catch (Exception $e) {
            Log::error('Error force deleting user:', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            throw new HttpResponseException(
                $this->error('An error occurred while permanently deleting user.', 403, null)
            );
        }
    }
}