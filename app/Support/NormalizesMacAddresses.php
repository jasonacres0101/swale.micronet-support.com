<?php

namespace App\Support;

trait NormalizesMacAddresses
{
    public static function normalizeMacAddress(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $normalized = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $value) ?? '');

        return $normalized !== '' ? $normalized : null;
    }
}
