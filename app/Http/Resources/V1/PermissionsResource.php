<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionsResource extends JsonResource
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
        $data['name'] = $this->name;
        $data['roles'] = $this->roles->map(function ($data) {
            return ucfirst($data->name);
        })->implode(",");
        $data['groups'] = $this->groups->map(function ($data) {
            return $data->description;
        })->implode(",");
        $data['created_at'] = dateTimeDisplay($this->created_at);
        return $data;
    }
}
