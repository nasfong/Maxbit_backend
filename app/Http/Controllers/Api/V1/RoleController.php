<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\V1\Role;
use App\Models\V1\MenuRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\RoleResource;
use App\Http\Resources\V1\RolesResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V1\RoleUsersResource;

class RoleController extends BaseApiController
{
    private $page = 'Role';
    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => [ 'store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => [ 'update']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = Role::latest()->get();
        return RolesResource::collection($data);
    }

    public function roleUser(Request $request, $name)
    {
        $data = User::role($name, 'sanctum')->latest()->paginate(12);
        return RoleUsersResource::collection($data);
    }

    public function listArr()
    {
        return Role::pluck('name', 'name')->toArray();
    }

    public function listIdArr()
    {
        return Role::pluck('name', 'id')->toArray();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            'menu_id' => 'nullable|array',
            'permission_name' => 'nullable|array',
        ], [
            'name.required' => 'The role name field is required.',
            'menu_id.required' => 'The menu field is required.'
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }

        $role = Role::create([
            'name' => strtolower($request->name),
        ]);
        if ($role) {
            if ($request->has('menu_id')) {
                app()['cache']->forget('spatie.permission.cache');
                // create menu
                MenuRole::where('role_id', $role->id)->delete();
                foreach ($request->menu_id as $key => $value) {
                    $menu_roll = new MenuRole;
                    $menu_roll->menu_id = $value;
                    $menu_roll->role_id = $role->id;
                    $menu_roll->save();
                }
                if ($request->has('permission_name')) {
                    // create permission
                    $role->syncPermissions($request->permission_name);
                }
            }
        }

        return $this->sendResponse('Success Added', $role);
    }

    public function show($id)
    {
        $role = Role::find($id);
        return $role ? new RoleResource($role) : '';
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $id,
            'menu_id' => 'nullable|array',
            'permission_name' => 'nullable|array',
        ], [
            'name.required' => 'The role name field is required.',
            'menu_id.required' => 'The menu field is required.'
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $role = Role::find($id);
        $role->update([
            'name' => strtolower($request->name),
        ]);
        if ($role) {
            if ($request->has('menu_id')) {
                app()['cache']->forget('spatie.permission.cache');
                // create menu
                MenuRole::where('role_id', $role->id)->delete();
                foreach ($request->menu_id as $key => $value) {
                    $menu_roll = new MenuRole;
                    $menu_roll->menu_id = $value;
                    $menu_roll->role_id = $role->id;
                    $menu_roll->save();
                }
                if ($request->has('permission_name')) {
                    // create permission
                    $role->syncPermissions($request->permission_name);
                }
            }
        }

        return $this->sendResponse('Success Added', $role);
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return $this->sendError("This role can't delete.");
        }

        if ($role = Role::find($id)) {
            DB::table('role_has_permissions')->where('role_id', $id)->delete();
            DB::table('model_has_roles')->where('role_id', $id)->delete();
            DB::table('menu_roles')->where('role_id', $id)->delete();
            $role->delete();
            return $this->sendResponse('Success deleted.');
        } else {
            return $this->sendError('The record not found!');
        }
    }
}
