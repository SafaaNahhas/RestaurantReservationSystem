<?php

namespace App\Http\Controllers\Api\RoleAndPermission;

use App\Http\Requests\Role\AddPermissionsRequest;
use App\Http\Requests\Role\RemovePermissionsRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
 use Illuminate\Http\Request;
use App\Http\Resources\RoleAndPermission\RoleResource;
use App\Http\Controllers\Controller;
use App\Services\Roles\RoleService;

class RoleController extends Controller
{
    protected $roleService;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * get all  roles
     * @param Request  $request
     * @return \Illuminate\Http\JsonResponse
      */
    public function index(Request $request)
    {
        $name = $request->input('name');
        $roles = $this->roleService->allRoles($name, false);
        return $this->paginated($roles, RoleResource::class ,'Get All Roles', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * create a new role
     *
     * @param StoreRoleRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRoleRequest $request)
    {
        $roleData = $request->validated();

        $role = $this->roleService->createRole($roleData);

        return $this->success(['role' => $role], 'Created Role', 201);

    }

    /**
     * Display the specified resource.
     */
    /**
     * get a specified role
     *
     * @param StoreRoleRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $role_id)
    {

        $data = $this->roleService->oneRole($role_id);

        return $this->success([ 'role' => $data['role'],'permissions' => $data['permissions']], 'Show Role', 200);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * update a specified role
     * @param int  $role_id
     * @param UpdateRoleRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRoleRequest $request, int $role_id)
    {
        $roleData = $request->validated();
        $role = $this->roleService->updateRole($role_id,  $roleData);
        return $this->success(['role' => $role], 'Update Role', 200);
    }

    /**
     * Remove the specified resource from storage.
     */

    /**
     * delete a specified role
     * @param int  $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function destroy(int $role_id)
    {
        $this->roleService->deleteRole($role_id);
        return $this->success(status: 204);

    }


    /**
     * add a permission to role
     * @param AddPermissionsRequest $request
     * @param int  $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPermissionToRole(AddPermissionsRequest $request, int $role_id)
    {
        $permissionsData = $request->validated();
        $this->roleService->addPermissionToRole($role_id, $permissionsData);
        return response()->json([
            'status' => 'success',
        ], 200);
    }
    /**
     * remove a permission from role
     * @param RemovePermissionsRequest $request
     * @param int $role_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePermissionFromRole(RemovePermissionsRequest $request, int $role_id)
    {
        $permissionsData = $request->validated();

        $this->roleService->removePermissionFromRole($role_id, $permissionsData);
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}