<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'cafe_id'     => ['required','integer','exists:cafes,id'],
            'category_id' => ['required','integer','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255','unique:products,slug'],
            'price'       => ['required','numeric','min:0','max:999999.99'],
            'is_active'   => ['boolean'],
            'description' => ['nullable','string'],
            'image_url'   => ['nullable','url'],
        ];
    }
    protected function prepareForValidation(): void {
        if ($this->has('price')) {
            $this->merge(['price' => number_format((float)$this->input('price'), 2, '.', '')]);
        }
    }
}