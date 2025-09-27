<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchStoreRequest extends FormRequest {
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array {
        return [
            'cafe_id'       => ['required','integer','exists:cafes,id'],
            'name'          => ['required','string','max:255'],
            'slug'          => ['nullable','string','max:255','unique:branches,slug'],
            'street'        => ['nullable','string','max:255'],
            'city'          => ['nullable','string','max:120'],
            'opening_hours' => ['nullable','array'],
            'is_active'     => ['boolean'],
        ];
    }
}
