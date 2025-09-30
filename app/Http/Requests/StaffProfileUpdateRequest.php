<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position' => [
                'nullable',
                'string',
                'max:120',
                Rule::notIn(['owner']) // Prevent owner assignment through staff profile updates
            ],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(
                    fn($q) =>
                    $q->where('cafe_id', $this->route('staffProfile')->cafe_id)
                ),
            ],
            'hire_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
