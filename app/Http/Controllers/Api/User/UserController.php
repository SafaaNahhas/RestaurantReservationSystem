<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {

        $this->userService = $userService;
    }

    /**
     * Lists all users with pagination.
     *
     * @param Request $request Contains query params.
     * @return JsonResponse Users collection.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);     // Get pagination limit
        $users = $this->userService->listUsers($perPage);           // Fetch paginated users
        return UserResource::collection($users)->response();        // Transform and return collection
    }

    /**
     * Creates new user.
     *
     * @param StoreUserRequest $request Validated user data.
     * @return JsonResponse Newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();                              // Get validated input
        $user = $this->userService->createUser($data);              // Create new user
        return self::success(new UserResource($user), 'User created successfully', 201);      // Return transformed resource
    }


    /**
     * Retrieves specific user.
     *
     * @param User $user User model instance.
     * @return JsonResponse User details.
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUser($user);             // Fetch user details
        return self::success(new UserResource($user), 'User retrieved successfully', 200);      // Transform and return
    }

    /**
     * Updates existing user.
     *
     * @param UpdateUserRequest $request Validated update data.
     * @param User $user User to update.
     * @return JsonResponse Updated user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();                                          // Get validated updates
        $updatedUser = $this->userService->updateUser($user, $data);            // Update user record
        return self::success(new UserResource($updatedUser), 'User updated successfully', 200);   // Return updated data
    }

    /**
     * Soft deletes user.
     *
     * @param User $user User to delete.
     * @return JsonResponse Empty response.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);                                              // Soft delete user
        return self::success([], 'User deleted successfully', 204);         // Return empty response
    }

    /**
     * Restores soft-deleted user.
     *
     * @param int $id User ID to restore.
     * @return JsonResponse Restored user.
     */
    public function restore(int $id): JsonResponse
    {
        $user = $this->userService->restoreUser($id);             // Restore soft-deleted user
        return self::success(new UserResource($user), 'User restored successfully', 200);       // Return restored user data
    }

    /**
     * Lists soft-deleted users.
     *
     * @param Request $request Contains pagination params.
     * @return JsonResponse Collection of trashed users.
     */
    public function trashedUsers(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);             // Get pagination limit
        $users = $this->userService->showDeletedUsers($perPage);            // Fetch soft-deleted users
        return UserResource::collection($users)->response();                // Return collection
    }

    /**
     * Permanently removes user.
     *
     * @param int $id User ID to permanently delete.
     * @return JsonResponse Empty response.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $this->userService->forceDeleteUser($id);                                   // Permanently delete user
        return self::success(null, 'User deleted permanently');      // Return success response
    }
}
