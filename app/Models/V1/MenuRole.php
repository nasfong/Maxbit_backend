<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    use HasFactory;
    protected $fillable = ["menu_id", "role_id"];

    public $incrementing = false;
    public $timestamps = false;
}
