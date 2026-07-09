<?php

namespace App\Traits;

trait GeneratesCode
{
    /**
     * Generate a unique sequential code like PAT-2024-00042.
     *
     * @param  string  $prefix  e.g. 'PAT', 'LAB', 'INV'
     * @param  string  $column  column to check uniqueness on
     */
    public static function generateCode(string $prefix, string $column = 'patient_code'): string
    {
        $year = now()->year;
        $base = "{$prefix}-{$year}-";

        $last = static::where($column, 'like', "{$base}%")
            ->orderByDesc($column)
            ->value($column);

        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
