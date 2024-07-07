<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data['id'] = $this->id;
        $data['fullname'] = $this->fullname;
        $data['email'] = $this->email;
        $data['photo'] = $this->photo ? asset($this->photo) : asset('/uploads/no_avatar.png');
        $data['created_at'] = dateTimeDisplay($this->created_at);
        return $data;
    }
}
