<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Concerns\FormatsMoney;

class OrderResource extends JsonResource {
    use FormatsMoney;

    public function toArray($request) {
        return [
            'id'                => $this->id,
            'cafe_id'           => $this->cafe_id,
            'branch_id'         => $this->branch_id,
            'user_id'           => $this->user_id,
            'status'            => $this->status,
            'payment_status'    => $this->payment_status,
            'payment_preference'=> $this->payment_preference,
            'table_number'      => $this->table_number,
            'total_price'       => $this->money($this->total_price),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'items'    => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'user'     => new UserResource($this->whenLoaded('user')),
            'cafe'     => new CafeResource($this->whenLoaded('cafe')),
            'branch'   => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}