<?php

namespace App\Http\Resources\V1;

use App\Models\V1\MenuRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data_role = [];
        $data_permission = [];
        $menu_roles = MenuRole::where('role_id', $this->id)->get();
        foreach ($menu_roles as $menu) {
            $data_role[] = strval($menu->menu_id);
        }

        $role_has_permission = DB::table('role_has_permissions')
            ->where('role_id', $this->id)
            ->get();

        foreach ($role_has_permission as $permission) {
            $get_permission = DB::table('permissions')->where('id', $permission->permission_id)->first();
            $data_permission[] = $get_permission->name;
        }


        $data = parent::toArray($request);
        $data['menu_id'] = $data_role;
        $data['permission_name'] = $data_permission;
        $data['summary_permissions'] = DB::select("SELECT pg.description FROM permissions p
        INNER JOIN permission_has_groups phg ON phg.permission_id = p.id
        INNER JOIN permission_groups pg ON pg.id= phg.permission_group_id
        INNER JOIN role_has_permissions rhp ON rhp.permission_id = phg.permission_id 
        WHERE rhp.role_id = ?
        GROUP BY pg.description,pg.created_at
        ORDER BY pg.created_at DESC", [$this->id]);
        return $data;
    }
}
