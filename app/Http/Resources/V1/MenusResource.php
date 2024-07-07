<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class MenusResource extends JsonResource
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
        $data['icon'] = $this->icon;
        $data['display_name'] = $this->display_name;
        $data['parent_name'] = $this->parent_id ? $this->parent->display_name : '';
        $data['order'] = $this->order;
        $data['group'] = $this->groups->map(function ($data) {
            return $data->description;
        })->implode(",");
        $data['roles'] = $this->roles->map(function ($data) {
            return ucfirst($data->name);
        })->implode(",");
        $data['created_at'] = dateTimeDisplay($this->created_at);
        return $data;
    }
}
