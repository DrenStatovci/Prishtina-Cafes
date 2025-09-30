<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CafeStoreRequest extends FormRequest {
    public function authorize(): bool 
    {
        return true;
    }
    
    public function rules(): array {
        return [
            'owner_id'    => ['required','integer','exists:users,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255','unique:cafes,slug'],
            'phone'       => ['nullable','string','max:50'],
            'email'       => ['nullable','email','max:255'],
            'description' => ['nullable','string'],
            'is_active'   => ['boolean'],
        ];
    }
}
