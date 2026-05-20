<?php

namespace App\Support;

class AdPlacements
{
    /** @return array<string, string> */
    public static function all(): array
    {
        return config('ad_placements', []);
    }

    public static function label(string $key): string
    {
        return self::all()[$key] ?? $key;
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::all());
    }
}
