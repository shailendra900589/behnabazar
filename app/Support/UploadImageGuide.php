<?php

namespace App\Support;

class UploadImageGuide
{
    /** @return array{size: string, ratio: string, format: string, max: string, tip: string}|null */
    public static function get(string $type): ?array
    {
        $guide = config('upload-images.'.$type);

        return is_array($guide) ? $guide : null;
    }

    public static function hint(string $type): string
    {
        $g = self::get($type);
        if (! $g) {
            return '';
        }

        return sprintf(
            'Recommended: %s (%s). %s. Max %s.',
            $g['size'],
            $g['ratio'],
            $g['format'],
            $g['max']
        );
    }

    public static function tip(string $type): string
    {
        return (string) (self::get($type)['tip'] ?? '');
    }
}
