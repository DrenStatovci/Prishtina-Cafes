<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'    => $this->id,
            'cafe_id' => $this->cafe_id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'street'=> $this->street,
            'city'  => $this->city,
            'opening_hours' => $this->opening_hours,
            'is_active' => (bool)$this->is_active,
            'created_at' => $this->created_at,

            'cafe' => new CafeResource($this->whenLoaded('cafe')),
        ];
    }
}