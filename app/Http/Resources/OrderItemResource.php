<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Concerns\FormatsMoney;

class OrderItemResource extends JsonResource {
    use FormatsMoney;

    public function toArray($request) {
        return [
            'product_id' => $this->product_id,
            'quantity'   => (int)$this->quantity,
            'unit_price' => $this->money($this->unit_price),
            'line_total' => $this->money($this->line_total),

            'product'    => new ProductResource($this->whenLoaded('product')),
        ];
    }
}