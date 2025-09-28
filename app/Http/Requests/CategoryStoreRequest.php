<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest {

    public function authorize(): bool 
    {
        return true;
    }
    
    public function rules(): array {
        return [
            'cafe_id'   => ['required','integer','exists:cafes,id'],
            'name'      => ['required','string','max:255'],
            'slug'      => ['nullable','string','max:255','unique:categories,slug'],
            'is_active' => ['boolean'],
        ];
    }
}