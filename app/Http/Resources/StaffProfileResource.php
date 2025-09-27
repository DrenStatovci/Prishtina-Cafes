<?php 

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'        => $this->id,
            'user_id'   => $this->user_id,
            'cafe_id'   => $this->cafe_id,
            'branch_id' => $this->branch_id,
            'position'  => $this->position,
            'hire_date' => $this->hire_date,
            'is_active' => (bool)$this->is_active,
            'created_at'=> $this->created_at,

            'user'   => new UserResource($this->whenLoaded('user')),
            'cafe'   => new CafeResource($this->whenLoaded('cafe')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}