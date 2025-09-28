<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Concerns\FormatsMoney;

class PaymentResource extends JsonResource {
    use FormatsMoney;

    public function toArray($request) {
        return [
            'id'            => $this->id,
            'order_id'      => $this->order_id,
            'amount'        => $this->money($this->amount),
            'method'        => $this->method,
            'status'        => $this->status,
            'transaction_id'=> $this->transaction_id,
            'payload'       => $this->payload,
            'processed_at'  => $this->processed_at,
            'created_at'    => $this->created_at,
        ];
    }
}