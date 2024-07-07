<?php

namespace App\Models\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'guard_name',
        'created_at',
        'updated_at'
    ];

    function groups()
    {
        return $this->belongsToMany(PermissionGroup::class, 'permission_has_groups', 'permission_id', 'permission_group_id');
    }

    public function scopeSearch($query, $request)
    {
        extract($request->all());
        $qry = $query;

        $sort = $sort ?? 'created_at';
        $order = $order ?? 'DESC';
       
        if (!empty($find)) $qry->where(function ($q2) use ($find) {
            $q2->where(DB::raw("CONCAT_WS(' ', name)"), 'like', "%" . $find . "%");
        });

        $qry->orderBy($sort, $order);
        return $qry;
    }
}
