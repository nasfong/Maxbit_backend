<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['group_id'] = $this->groups->map(function ($data) {
            return $data->id;
        })->implode(",");
        $data['role_id'] = $this->roles->map(function ($data) {
            return $data->id;
        })->implode(",");

        return $data;
    }
}
