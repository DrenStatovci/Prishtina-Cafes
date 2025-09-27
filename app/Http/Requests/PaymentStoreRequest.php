<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest {
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array {
        return [
            'amount'   => ['required','numeric','min:0.01','max:999999.99'],
            'method'   => ['required','string','max:50'],
            'payload'  => ['sometimes','array'],
        ];
    }
    protected function prepareForValidation(): void {
        if ($this->has('amount')) {
            $this->merge(['amount' => number_format((float)$this->input('amount'), 2, '.', '')]);
        }
    }
}