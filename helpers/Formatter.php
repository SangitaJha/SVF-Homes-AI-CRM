<?php

declare(strict_types=1);

namespace App\Helpers;

final class Formatter
{
    public static function title(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', trim($value)));
    }

    public static function initials(?string $value): string
    {
        $parts = preg_split('/\s+/', trim((string)$value)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials ?: 'SV';
    }
}