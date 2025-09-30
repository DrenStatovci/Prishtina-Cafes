<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                // Unique tuple (user_id, cafe_id, branch_id/null)
                Rule::unique('staff_profiles', 'user_id')->where(function ($q) {
                    $q->where('cafe_id', (int) $this->input('cafe_id'));

                    if ($this->filled('branch_id')) {
                        $q->where('branch_id', (int) $this->input('branch_id'));
                    } else {
                        $q->whereNull('branch_id');
                    }
                }),
            ],

            'cafe_id' => ['required', 'integer', 'exists:cafes,id'],

            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(
                    fn($q) =>
                    $q->where('cafe_id', (int) $this->input('cafe_id'))
                ),
            ],

            'position' => [
                'required',
                'string',
                'max:120',
                Rule::notIn(['owner']) // Prevent owner assignment through staff profiles
            ],
            'hire_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
