<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Concerns\FormatsMoney;

class ProductResource extends JsonResource {
    use FormatsMoney;

    public function toArray($request) {
        return [
            'id'         => $this->id,
            'cafe_id'    => $this->cafe_id,
            'category_id'=> $this->category_id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'price'      => $this->money($this->price),
            'is_active'  => (bool)$this->is_active,
            'description'=> $this->description,
            'image_url'  => $this->image_url,
            'created_at' => $this->created_at,

            'category'   => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}