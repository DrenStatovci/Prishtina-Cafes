<?php 

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'       => $this->id,
            'cafe_id'  => $this->cafe_id,
            'name'     => $this->name,
            'slug'     => $this->slug,
            'is_active'=> (bool)$this->is_active,
            'created_at'=> $this->created_at,

            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}