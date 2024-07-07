<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\MenuGroupsResource;
use Illuminate\Support\Str;
use App\Models\V1\MenuGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuGroupController extends BaseApiController
{
    private $page = 'Menu Group';

    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => ['store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => ['update']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data =  MenuGroup::latest()->paginate(12);
        return MenuGroupsResource::collection($data);
    }

    public function menuGroupArr()
    {
        return MenuGroup::latest()->pluck('description', 'id')->toArray();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|unique:menu_groups,description',
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }

        $name = Str::slug($request->description, '_');
        $data = MenuGroup::create([
            'name' => $name,
            'description' => $request->description,
            'order' => MenuGroup::count() + 1,
        ]);
        return $this->sendResponse('Success added.', $data);
    }

    public function show($id)
    {
        return MenuGroup::find($id);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|unique:menu_groups,description,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendValidation($validator->errors());
        }
        $data = MenuGroup::find($id);

        $name = Str::slug($request->description, '_');
        $data->update([
            'name' => $name,
            'description' => $request->description,
            'order' => $request->order,
        ]);
        return $this->sendResponse('Success updated.', $data);
    }

    public function destroy($id)
    {
        try {
            if ($data = MenuGroup::find($id)) {
                DB::table('menu_has_groups')->where('menu_group_id', $id)->delete();
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
