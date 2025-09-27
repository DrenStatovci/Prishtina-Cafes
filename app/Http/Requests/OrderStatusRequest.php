<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\Enums;

class OrderStatusRequest extends FormRequest {
    public function authorize(): bool 
    {
        return true; 
    }

    public function rules(): array {
        return ['status' => ['required', Rule::in(Enums::ORDER_STATUS)]];
    }
}
