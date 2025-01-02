<?php

namespace App\Services;

 use App\Http\Resources\PermissionResource;
 use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService
{
    /**
     * show all permissions
      * @return LengthAwarePaginator $permissions 
     */
    public function allPermissions()
    {
        try {
            $permissions = Permission::paginate(6);
            return  $permissions;
        } catch (Exception $e) {
            Log::error("error in get all permissions"  . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * show a permission 
     * @param int $permission_id      
     * @return PermissionResource $permission 
     */
    public function onePermission($permission_id)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
            $permission = PermissionResource::make($permission);
            return $permission;
        }catch (ModelNotFoundException $e) {
            Log::error("error in  show a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in  show a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } 
    }
    /**
     * create a  new permission
     * @param  array $permissionData  
     * @return PermissionResource permission  
     */
    public function createPermission($permissionData)
    {
        try {
            $permission = Permission::create($permissionData);
            $permission  = PermissionResource::make($permission);
            return  $permission;
        } catch (Exception $e) {
            Log::error("error in create a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * update a permission
     * @param int $permission_id      
     * @param  array $permissionData  
     * @return PermissionResource permission  
     */
    public function updatePermission(int $permission_id, $permissionData)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
            $permission->update($permissionData);
            $permission = PermissionResource::make(Permission::find($permission->id));
            return  $permission;
        } catch (ModelNotFoundException $e) {
            Log::error("error in   update a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }catch (Exception $e) {
            Log::error("error in   update a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        } 
    }

    /**
     *  delete a  permission
     * @param int $permission_id      
     */
    public function deletePermission(int $permission_id)
    {
        try {
            $permission = Permission::findOrFail($permission_id);
            $permission->delete();
        }catch (ModelNotFoundException $e) {
            Log::error("error in soft delete a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in  soft delete a  permission"  . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
 }