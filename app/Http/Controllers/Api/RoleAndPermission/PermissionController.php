<?php

namespace App\Http\Controllers\Api\RoleAndPermission;

use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
 use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleAndPermission\PermissionResource;

class PermissionController extends Controller
{
    protected $permissionService;
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * get all  permissions
     * @param Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
         $permissions = $this->permissionService->allPermissions();
        return $this->paginated($permissions, PermissionResource::class ,'Get All Permissions', 200);

    }
    /**
     * Store a newly created resource in storage.
     */
    /**
     * create a new permission
     * @param StorePermissionRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePermissionRequest $request)
    {
        $categoryData = $request->validated();

        $permission = $this->permissionService->createPermission($categoryData);
      return $this->success( [ 'permission' => $permission,],"Created Permission",201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * get a  specified permission
     * @param int  $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($permission_id)
    {
        $permission = $this->permissionService->onePermission($permission_id);

        return $this->success( [ 'permission' => $permission,],"Show Permission",200);

    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * update a  specified permission
     * @param UpdatePermissionRequest $request
     * @param int  $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePermissionRequest $request, int $permission_id)
    {
        $permissionData = $request->validated();
        $permission = $this->permissionService->updatePermission($permission_id,  $permissionData);
        return $this->success( [ 'permission' => $permission,],"Update Permission",200);

    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * delete a  specified permission
     * @param int  $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($permission_id)
    {
        $this->permissionService->deletePermission($permission_id);
        return $this->success(status: 204);
    }
}
