<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;
    protected $fillable = ["id", "name", "description", "type", "order", "created_at", "updated_at"];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_has_groups', 'permission_group_id', 'permission_id');
    }
}
