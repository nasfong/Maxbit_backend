<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class RolesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $role_name = $this->name;
        $data['id'] = $this->id;
        $data['name'] = $role_name;
        $data['display_name'] = ucfirst($role_name);
        $data['total_users'] = User::whereHas(
            'roles',
            function ($q) use ($role_name) {
                $q->where('name', $role_name);
            }
        )->count();
        $data['summary_permissions'] = DB::select("SELECT pg.description FROM permissions p
        INNER JOIN permission_has_groups phg ON phg.permission_id = p.id
        INNER JOIN permission_groups pg ON pg.id= phg.permission_group_id
        INNER JOIN role_has_permissions rhp ON rhp.permission_id = phg.permission_id 
        WHERE rhp.role_id = ?
        GROUP BY pg.description,pg.created_at
        ORDER BY pg.created_at DESC
        LIMIT 5", [$this->id]);
        
        return $data;
    }
}
