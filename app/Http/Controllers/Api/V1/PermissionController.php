<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\V1\Role;
use Illuminate\Http\Request;
use App\Models\V1\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\V1\PermissionHasGroup;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V1\PermissionsResource;

class PermissionController extends BaseApiController
{
    private $page = 'Permission';

    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => ['store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => ['update']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Permission::search($request)->latest()->paginate(12);
        return PermissionsResource::collection($data);
    }

    public function permissionList()
    {
        $permissions = Permission::pluck('name');

        return $this->sendResponse(null, (object) $permissions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name',
            'permission_group' => 'required',
            'roles' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $permission = Permission::create([
            'name' => $request->name,
        ]);

        if ($permission) {
            try {
                app()['cache']->forget('spatie.permission.cache');
                /** Add permissionn_id and permission_group_id
                 * To Table Permission_has_group
                 */
                $permission->groups()->attach($request->permission_group);
                /** Add Role to Permission */
                if (!empty($request->roles)) {
                    foreach ($request->roles as $role) {
                        $role = Role::where('name', '=', $role)->firstOrFail();
                        $permission = Permission::where('name', '=', $request->name)->first();
                        $role->givePermissionTo($permission);
                    }
                }
            } catch (\Exception $e) {
                $permission->delete();
                return $this->sendError($e->getMessage());
            }
        }
        return $this->sendResponse('Success added.', $permission);
    }

    public function show($id)
    {
        $permission = Permission::find($id);
        $permission_has_group = PermissionHasGroup::where('permission_id', $id)->first();

        $data['id'] = $permission->id;
        $data['name'] = $permission->name;
        $data['roles'] = $permission->roles->map(function ($data) {
            return $data->name;
        })->implode(",");
        $data['permission_group'] =  $permission_has_group->permission_group_id ?? '';

        return $permission ? $data : null;
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name,' . $id,
            'permission_group' => 'required',
            'roles' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }

        if ($permission = Permission::find($id)) {
            $permission->update([
                'name' => $request->name,
            ]);
            $permission->groups()->sync($request->permission_group);
            if (!empty($request->roles)) {
                app()['cache']->forget('spatie.permission.cache');
                DB::table('role_has_permissions')
                    ->where('permission_id', $permission->id)->delete();
                foreach ($request->roles as $role) {
                    $r = Role::where('name', '=', $role)->firstOrFail();
                    $permission = Permission::where('name', '=', $request->name)->first();

                    // $r->revokePermissionTo($permission->name);
                    $r->givePermissionTo($permission->name);
                }
            }
            return $this->sendResponse('Success updated.', $permission);
        } else {
            return $this->sendError('The record not found!');
        }
    }

    public function destroy($id)
    {
        if ($permission = Permission::find($id)) {
            DB::table('model_has_permissions')->where('permission_id', $id)->delete();
            DB::table('permission_has_groups')->where('permission_id', $id)->delete();
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
            $permission->delete();
            return $this->sendResponse('Success deleted.');
        } else {
            return $this->sendError('The record not found!');
        }
    }
}
