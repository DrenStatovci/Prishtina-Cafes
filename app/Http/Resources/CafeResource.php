<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\CategoryResource;

class CafeResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'    => $this->id,
            'owner_id' => $this->owner_id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'description' => $this->description,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'branches'   => BranchResource::collection($this->whenLoaded('branches')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}