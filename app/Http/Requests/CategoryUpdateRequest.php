<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @method mixed route(string|null $key = null)
 */

class CategoryUpdateRequest extends FormRequest {

    public function authorize(): bool 
    {
        return true;
    }
    
    
    public function rules(): array {
        $id = $this->route('category')->id ?? null;
        return [
            'name'      => ['sometimes','string','max:255'],
            'slug'      => ['sometimes','string','max:255', Rule::unique('categories','slug')->ignore($id)],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}
