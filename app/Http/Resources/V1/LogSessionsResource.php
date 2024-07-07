<?php

namespace App\Http\Resources\V1;

use App\Models\V1\LogSession;
use Illuminate\Http\Resources\Json\JsonResource;

class LogSessionsResource extends JsonResource
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
        $data['last_login'] = $this->last_login ? dateTimeDisplay($this->last_login) : '';
        $data['time'] = time_ago($this->last_login);
        $data['created_at'] = dateDisplay($this->created_at);
        $data['data'] = !empty($this->data) ? json_decode($this->data) : '';
        $data['agent_summary'] = mb_strimwidth($this->agent, 0, 30, '...');
        return $data;
    }
}
