<?php

namespace App\Support;

trait NormalizesSerialNumbers
{
    public static function normalizeSerialNumber(?string $serialNumber): ?string
    {
        if ($serialNumber === null) {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $serialNumber);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        return strtoupper($normalized);
    }
}
