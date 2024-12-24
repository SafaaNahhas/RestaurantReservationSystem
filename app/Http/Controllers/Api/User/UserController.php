<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
      
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $users = $this->userService->listUsers($perPage);
        return UserResource::collection($users)->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->createUser($data);
        return $this->success(new UserResource($user), 'User created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUser($user);
        return $this->success(new UserResource($user), 'User retrieved successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $updatedUser = $this->userService->updateUser($user, $data);
        return $this->success(new UserResource($updatedUser), 'User updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);
        return $this->success([], 'User deleted successfully', 204);
    }

    /**
     * Restore a specific deleted user.
     * @param $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $user = $this->userService->restoreUser($id);
        return $this->success(new UserResource($user), 'User restored successfully', 200);
    }

    /**
     * List trashed users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trashedUsers(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $users = $this->userService->showDeletedUsers($perPage);
        return UserResource::collection($users)->response();
    }

    /**
     * Permanently delete user.
     *
     * @param $id
     * @return JsonResponse
     */
    public function forceDelete($id): JsonResponse
    {
        $this->userService->forceDeleteUser($id);
        return $this->success(null, 'User deleted permanently');
    }
}
