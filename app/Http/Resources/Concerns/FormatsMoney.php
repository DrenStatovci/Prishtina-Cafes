<?php 

namespace App\Http\Resources\Concerns;

trait FormatsMoney {
    protected function money($value): string {
        return number_format((float)$value, 2, '.', '');
    }
}
