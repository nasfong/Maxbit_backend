<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];

        $data['id'] = $this->id;
        $data['fullname'] = $this->fullname;
        $data['first_name'] = $this->first_name;
        $data['last_name'] = $this->last_name;
        $data['phone'] = $this->phone;
        $data['email'] = $this->email;
        $data['roles'] = $this->roles->map(function ($data) {
            return $data->name;
        })->implode(",");
        $data['photo'] = $this->photo ? asset($this->photo) : asset('/uploads/no_avatar.png');
        $data['username'] = $this->username;
        $data['last_login'] = $this->currentLog ? dateTimeDisplay($this->currentLog->last_login) : '';
        $data['created_at'] = dateDisplay($this->created_at);
        $data['activated'] = $this->activated;
        $data['deleted_at'] = $this->deleted_at;
        return $data;
    }
}
