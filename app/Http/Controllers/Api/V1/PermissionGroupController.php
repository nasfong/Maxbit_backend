<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PermissionGroupsResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\V1\PermissionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermissionGroupController extends BaseApiController
{
    private $page = 'Permission Group';

    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => ['store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => ['update']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = PermissionGroup::latest()->paginate(12);
        return PermissionGroupsResource::collection($data);
    }

    public function groupWithPermissions()
    {
       $groups = PermissionGroup::with('permissions')->get();
        $data = [];
       foreach($groups as $group){
           $data_permission = [];
           foreach($group->permissions as $permission){
            $data_permission[] = [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
            ];
           }
        $data[] = [
            'group_id' => $group->id,
            'group_description' => $group->description,
            'permissions' => $data_permission
        ];
       }
       return $data;
    }

    public function listArr()
    {
        return PermissionGroup::latest()->pluck('name', 'id')->toArray();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|unique:permission_groups,description',
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $data = PermissionGroup::create([
            'name' => Str::slug($request->description, '_'),
            'description' => $request->description,
            'type' => $request->type,
            'order' => PermissionGroup::count() + 1,
        ]);
        return $this->sendResponse('Success added.', $data);
    }

    public function show($id)
    {
        return PermissionGroup::find($id);
    }

    public function update(Request $request, $id)
    {
        $data = PermissionGroup::find($id);
        $validator = Validator::make($request->all(), [
            'description' => 'required|unique:permission_groups,description,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $data->update([
            'name' => Str::slug($request->description, '_'),
            'description' => $request->description,
            'order' => $request->order,
            'type' => $request->type,
        ]);
        return $this->sendResponse('Success updated.', $data);
    }

    public function destroy($id)
    {
        try {
            if ($data = PermissionGroup::find($id)) {
                DB::table('permission_has_groups')->where('permission_group_id', $id)->delete();
                $data->delete();
                return $this->sendResponse('Success deleted.');
            } else {
                return $this->sendError('The record not found!');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
