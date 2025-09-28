<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\Enums;

class OrderStoreRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'cafe_id'            => ['required','integer','exists:cafes,id'],
            'branch_id'          => ['nullable','integer','exists:branches,id'],
            'items'              => ['required','array','min:1'],
            'items.*.product_id' => ['required','integer','exists:products,id'],
            'items.*.quantity'   => ['required','integer','min:1','max:50'],
            'payment_preference' => ['nullable', Rule::in(Enums::PAYMENT_PREF)],
            'table_number'       => ['nullable','integer','min:1','max:200'],
        ];
    }
}
