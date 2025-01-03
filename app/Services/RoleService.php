<?php

namespace App\Services;

 use App\Http\Resources\RoleResource;
use App\Http\Resources\PermissionResource;
  use Exception;
use Illuminate\Support\Facades\Log;
 use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    /**
     * show all roles
     * @param string $name  
     * @return LengthAwarePaginator $roles 
     */
    public function allRoles($name, $deletedRole)
    {

                $roles = Role::where('name','!=','admin')->paginate(4);
              return  $roles;
              try {
  } catch (Exception $e) {
            Log::error("error in get all roles"  . $e->getMessage());

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
     * show a role and all  her permissions
     * @param  int $role  
     * @return array RoleResource $role and PermissionResource $permissions
     */
    public function oneRole($role_id)
    {
        try {
            $role = Role::findOrFail($role_id);
            
            $permissions = $role->load('permissions')->permissions;
            $role = RoleResource::make($role);

            $permissions = $permissions->isNotEmpty() ? PermissionResource::collection($permissions) : [];

            return [
                'role' => $role,
                'permissions' =>  $permissions
            ];
        } catch (ModelNotFoundException $e) {
            Log::error("error in  show a role" . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }catch (Exception $e) {
            Log::error("error in  show a  role"  . $e->getMessage());

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
     * create a  new role
     * @param  array $roleData  
     * @return RoleResource role  
     */
    public function createRole($roleData)
    {
        try {
            $role = Role::create($roleData);
            $role  = RoleResource::make($role);
            return  $role;
        } catch (Exception $e) {
            Log::error("error in create a  role"  . $e->getMessage());
 
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
     * update a role
     * @param int $role_id  
     * @param  array $roleData  
     * @return RoleResource role  
     */
    public function updateRole(int $role_id, $roleData)
    {
        try {
            $role = Role::findOrFail($role_id);
        
             $role->update($roleData);
            $role = RoleResource::make(Role::find($role->id));
            return  $role;
        }catch (ModelNotFoundException $e) {
            Log::error("error in  update a role" . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (Exception $e) {
            Log::error("error in   update a  role"  . $e->getMessage());
 
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
     *  delete a  role
     * @param int $role_id 
     */
    public function deleteRole(int $role_id)
    {
        try {
            $role = Role::findOrFail($role_id);
      
 
             $role->delete();
           } catch (ModelNotFoundException $e) {
            Log::error("error in  delete a  role" . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }catch (Exception $e) {
            Log::error("error in  soft delete a  role"  . $e->getMessage());

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
     *  add permission to role
     * @param int $role_id  
     *  @param array permissionsData
     */
    public function addPermissionToRole($role_id, $permissionsData)
    {
        try {
            $role = Role::findOrFail($role_id);
        
             $permissionsData = array_unique(array_filter($permissionsData, function ($num) {
                return $num > 0;
            }));

            foreach ($permissionsData as $i) {
                $role->givePermissionTo(Permission::findOrFail($i));
            }
        } catch (ModelNotFoundException $e) {
            Log::error("error in add permission to role" . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }
        catch (Exception $e) {
            Log::error("error in add permission to role" . $e->getMessage());
 
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
     *  remove permission from role
     * @param int $role_id  
     *  @param array permissionsData
     */
    public function removePermissionFromRole($role_id, $permissionsData)
    {
        try {
            $role = Role::findOrFail($role_id);

            $permissionsData = array_unique(array_filter($permissionsData, function ($num) {
                return $num > 0;
            }));
            foreach ($permissionsData as $i) {
                $role->revokePermissionTo(Permission::findOrFail($i));
            }
        } catch (ModelNotFoundException $e) {
            Log::error("error in remove permission from role" . $e->getMessage());
 
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        }catch (Exception $e) {
            Log::error("error in remove permission from role" . $e->getMessage());
 
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