<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    /**
     * Public URL for a file on the "public" disk (banners, products, etc.).
     */
    public static function url(?string $relativePath): string
    {
        if (! $relativePath || trim($relativePath) === '') {
            return self::placeholder();
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return $relativePath;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        return Storage::disk('public')->url($relativePath);
    }

    /**
     * Copy from storage/app/public into public/storage (and public_html on Hostinger).
     */
    public static function publish(?string $relativePath): bool
    {
        if (! $relativePath || str_starts_with($relativePath, 'http')) {
            return true;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $source = storage_path('app/public/'.$relativePath);

        if (! is_file($source)) {
            return false;
        }

        $publicFile = public_path('storage/'.$relativePath);
        File::ensureDirectoryExists(dirname($publicFile));
        File::copy($source, $publicFile);

        $publicHtml = HostingerPublicSync::detectPublicHtml();
        if ($publicHtml !== null) {
            $laravelPublic = realpath(public_path());
            $htmlPublic = realpath($publicHtml);
            if ($htmlPublic && $laravelPublic && $htmlPublic !== $laravelPublic) {
                $htmlFile = rtrim($publicHtml, '/\\').'/storage/'.$relativePath;
                File::ensureDirectoryExists(dirname($htmlFile));
                File::copy($source, $htmlFile);
            }
        }

        return true;
    }

    public static function unpublish(?string $relativePath): void
    {
        if (! $relativePath || str_starts_with($relativePath, 'http')) {
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        $paths = [
            public_path('storage/'.$relativePath),
        ];

        $publicHtml = HostingerPublicSync::detectPublicHtml();
        if ($publicHtml !== null) {
            $paths[] = rtrim($publicHtml, '/\\').'/storage/'.$relativePath;
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Mirror all public uploads to public/storage and public_html (run on deploy).
     */
    public static function republishAll(): void
    {
        StoragePublicLink::ensure();

        $source = storage_path('app/public');
        if (! is_dir($source)) {
            return;
        }

        $link = public_path('storage');
        if (is_link($link)) {
            @unlink($link);
        }
        if (is_dir($link)) {
            File::deleteDirectory($link);
        }
        File::copyDirectory($source, $link);

        $publicHtml = HostingerPublicSync::detectPublicHtml();
        $laravelPublic = realpath(public_path());
        if ($publicHtml !== null && $laravelPublic && realpath($publicHtml) !== $laravelPublic) {
            $storageDest = rtrim($publicHtml, '/\\').'/storage';
            if (is_link($storageDest) || is_dir($storageDest)) {
                @unlink($storageDest);
                @rmdir($storageDest);
            }
            File::copyDirectory($source, $storageDest);
        }
    }

    public static function placeholder(): string
    {
        return 'data:image/svg+xml,'.rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="200" viewBox="0 0 400 200">'
            .'<rect fill="#f1f5f9" width="400" height="200"/>'
            .'<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="14">No image</text></svg>'
        );
    }
}
