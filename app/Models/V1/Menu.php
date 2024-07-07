<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ["id", "parent_id", "name", "display_name", "icon", "font_icon", "order", "url", "hide", "has_children", "position", "created_at", "updated_at"];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    function groups()
    {
        return $this->belongsToMany(MenuGroup::class, 'menu_has_groups', 'menu_id', 'menu_group_id');
    }

    function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_roles', 'menu_id', 'role_id');
    }
}
