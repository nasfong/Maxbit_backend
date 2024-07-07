<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\MenuResource;
use App\Http\Resources\V1\MenusResource;
use Exception;
use App\Models\V1\Menu;
use App\Models\V1\MenuGroup;
use App\Models\V1\MenuRole;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends BaseApiController
{
    private $page = 'Menu';

    function __construct()
    {
        $this->middleware('permission:' . $this->page . ' List', ['only' => ['index']]);
        $this->middleware('permission:' . $this->page . ' Create', ['only' => ['store']]);
        $this->middleware('permission:' . $this->page . ' Edit', ['only' => ['update']]);
        $this->middleware('permission:' . $this->page . ' Delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = Menu::latest()->get();
        return MenusResource::collection($data);
    }

    public function parentMenu()
    {
        return Menu::whereNull('parent_id')
            ->latest()
            ->pluck('display_name', 'id')
            ->toArray();
    }

    public function menuListWithParent()
    {
        $menus = Menu::with(['childs'])->whereNull('parent_id')->orderBy('name')->get();
        $data = [];

        foreach ($menus as $menu) {
            $data_parent = [];
            foreach ($menu->childs as $parent) {
                $data_parent[] = [
                    'id' => $parent->id,
                    'name' => $parent->display_name
                ];
            }

            $data[] = [
                'id' => $menu->id,
                'name' => $menu->display_name,
                'parent' => $data_parent
            ];
        }
        return $data;
    }

    public function menuList()
    {
        $data = [];
        $menu_groups = MenuGroup::orderBy('order', 'DESC')->get();
        foreach ($menu_groups as $menu_group) {
            $data_menu = [];
            $menus = DB::select("SELECT * FROM model_has_roles mhr 
            INNER JOIN menu_roles mr ON mr.role_id = mhr.role_id 
            INNER JOIN menus m ON m.id = mr.menu_id
            INNER JOIN menu_has_groups mhg ON mhg.menu_id = m.id
            WHERE mhr.model_id = ? 
             AND m.position = ?
             AND m.hide = 0 
             AND mhg.menu_group_id =?
            ORDER BY m.order ASC", [
                auth()->id(), 'sidebar_left', $menu_group->id
            ]);

            foreach ($menus as $menu) {
                $data_menu[] = [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'parent_id' => $menu->parent_id,
                    'display_name' => $menu->display_name,
                    'icon' => $menu->icon,
                    'url' => $menu->url,
                    'hide' => $menu->hide,
                    'has_children' => $menu->has_children,
                ];
            }
            if (count($data_menu) > 0)
                $data[$menu_group->description] = $data_menu;
        }

        return $this->sendResponse(null, $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|unique:menus,display_name',
            'role_id' => 'required|array',
            'menu_group' => 'required'
        ], [
            'display_name.required' => 'The name is field is required..',
            'role_id.required' => 'The role is field is required..',
        ]);

        if ($validator->fails())
            return $this->sendValidation($validator->errors());

        $data_input = [
            'name' => Str::slug($request->display_name, '_'),
            'display_name' => $request->display_name,
            'parent_id' => empty($request->parent_id) ? null : $request->parent_id,
            'icon' => $request->icon,
            'font_icon' => $request->font_icon,
            'url' => $request->url,
            'order' => Menu::count() + 1,
            'hide' => $request->hide ?? 0,
            'position' => $request->position ?? 'sidebar_left',
            'has_children' => $request->has_children ? true : false,
        ];

        if ($menu = Menu::create($data_input)) {
            $menu->groups()->attach($request->menu_group);
            try {
                foreach ($request->role_id as $key => $value) {
                    $menu_roll = new MenuRole;
                    $menu_roll->menu_id = $menu->id;
                    $menu_roll->role_id = $value;
                    $menu_roll->save();
                }
            } catch (Exception $e) {
                $menu->delete();
            }

            return $this->sendResponse('Success added.', $menu);
        }
    }

    public function show($id)
    {
        return new MenuResource(Menu::find($id));
    }

    public function update(Request $request, $id)
    {
        $data = Menu::find($id);
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|unique:menus,display_name,' . $id,
            'role_id' => 'required|array',
            'menu_group' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendValidation($validator->errors());

        $data_input = [
            'name' => Str::slug($request->display_name, '_'),
            'display_name' => $request->display_name,
            'parent_id' => empty($request->parent_id) ? null : $request->parent_id,
            'icon' => $request->icon,
            'order' => $request->order,
            'font_icon' => $request->font_icon,
            'url' => $request->url,
            'hide' => $request->hide ?? 0,
            'position' => $request->position ?? 'sidebar_left',
            'has_children' => $request->has_children ? true : false,
        ];

        if ($data->update($data_input)) {
            $data->groups()->sync($request->menu_group);
            MenuRole::where('menu_id', $id)->delete();
            foreach ($request->role_id as $key => $value) {
                $menu_roll = new MenuRole;
                $menu_roll->menu_id = $id;
                $menu_roll->role_id = $value;
                $menu_roll->save();
            }

            return $this->sendResponse('Success updated.', $data);
        }
    }

    public function destroy($id)
    {
        try {
            if ($menu = Menu::find($id)) {
                DB::table('menu_has_groups')->where('menu_id', $id)->delete();
                DB::table('menu_roles')->where('menu_id', $id)->delete();
                $menu->delete();
                return $this->sendResponse('Success deleted.');
            } else {
                return $this->sendError('The record not found!');
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
