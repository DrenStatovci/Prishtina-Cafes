<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


/**
 * @method mixed route(string|null $key = null)
 */

class BranchUpdateRequest extends FormRequest {

    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array {
        $id = $this->route('branch')->id ?? null;
        return [
            'name'          => ['sometimes','string','max:255'],
            'slug'          => ['sometimes','string','max:255', Rule::unique('branches','slug')->ignore($id)],
            'street'        => ['sometimes','nullable','string','max:255'],
            'city'          => ['sometimes','nullable','string','max:120'],
            'opening_hours' => ['sometimes','nullable','array'],
            'is_active'     => ['sometimes','boolean'],
        ];
    }
}