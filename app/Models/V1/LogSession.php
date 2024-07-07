<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogSession extends Model
{
    use HasFactory;
    protected $fillable = ["id","user_id","token_id","ip","agent","last_login","data","created_at","updated_at"];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
