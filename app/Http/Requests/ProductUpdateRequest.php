<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest {
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array {
        $id = $this->route('product')->id ?? null;
        return [
            'category_id' => ['sometimes','integer','exists:categories,id'],
            'name'        => ['sometimes','string','max:255'],
            'slug'        => ['sometimes','string','max:255', Rule::unique('products','slug')->ignore($id)],
            'price'       => ['sometimes','numeric','min:0','max:999999.99'],
            'is_active'   => ['sometimes','boolean'],
            'description' => ['sometimes','nullable','string'],
            'image_url'   => ['sometimes','nullable','url'],
        ];
    }
    
    protected function prepareForValidation(): void {
        if ($this->has('price')) {
            $this->merge(['price' => number_format((float)$this->input('price'), 2, '.', '')]);
        }
    }
}