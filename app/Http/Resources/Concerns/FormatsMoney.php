<?php 

namespace App\Http\Resources\Concerns;

trait FormatsMoney
{
    protected function money($value): string
    {
        $s = (string) $value;
        if (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);
        }
        $parts = explode('.', $s, 2);
        $whole = $parts[0] ?? '0';
        $frac  = str_pad($parts[1] ?? '0', 2, '0');
        return $whole . '.' . substr($frac, 0, 2);
    }
}
