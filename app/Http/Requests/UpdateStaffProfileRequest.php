<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StaffProfileUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array
    {
        $currentId = $this->route('staff_profile')->id(); // helper nga BaseFormRequest nëse e përdor

        return [
            'user_id'   => ['sometimes','integer','exists:users,id',
                Rule::unique('staff_profiles','user_id')
                    ->ignore($currentId) // mos llogarit rekordin aktual
                    ->where(function ($q) {
                        $cafeId = (int)($this->input('cafe_id') ?? optional($this->route('staff_profile'))->cafe_id);
                        $branch = $this->input('branch_id', optional($this->route('staff_profile'))->branch_id);

                        $q->where('cafe_id', $cafeId);
                        $branch !== null ? $q->where('branch_id', (int)$branch) : $q->whereNull('branch_id');
                    }),
            ],
            'cafe_id'   => ['sometimes','integer','exists:cafes,id'],
            'branch_id' => [
                'sometimes','nullable','integer',
                Rule::exists('branches','id')->where(function ($q) {
                    $cafeId = (int)($this->input('cafe_id') ?? optional($this->route('staff_profile'))->cafe_id);
                    $q->where('cafe_id', $cafeId);
                }),
            ],
            'position'  => ['sometimes','nullable','string','max:120'],
            'hire_date' => ['sometimes','nullable','date'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}
