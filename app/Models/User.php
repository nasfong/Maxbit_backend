<?php

namespace App\Models;

use App\Models\V1\LogSession;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;
    public $guard_name = 'sanctum';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'parent_id',
        'author_id',
        'phone',
        'email',
        'password',
        'photo',
        'status',
        'activated',
        'first_login',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getFullnameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function currentLog()
    {
        return $this->hasOne(LogSession::class, 'user_id', 'id')->latest();
    }

    public function log()
    {
        return $this->hasOne(LogSession::class, 'user_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(LogSession::class, 'user_id', 'id');
    }

    public static function myCreate(array $request)
    {
        try {
            return self::create($request);
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function scopeSearch($query, $request)
    {
        extract($request->all());
        $qry = $query;

        $sort = $sort ?? 'created_at';
        $order = $order ?? 'DESC';
        // if (!empty($trashed) && $trashed == 1) $qry->withTrashed();
        if (!empty($get_type)) {
            if ($get_type == 'trashed') $qry->onlyTrashed();
            elseif ($get_type == 'all') $qry->withTrashed();
        }

        if (!empty($find)) $qry->where(function ($q2) use ($find) {
            $q2->where(DB::raw("CONCAT_WS(' ', first_name, last_name, email)"), 'like', "%" . $find . "%");
        });


        $qry->orderBy($sort, $order);
        return $qry;
    }
}
