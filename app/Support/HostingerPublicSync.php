<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class HostingerPublicSync
{
    /** Marker in generated public_html/index.php */
    public const BOOTSTRAP_MARKER = 'Behna Bazar Hostinger bootstrap';

    /**
     * Paths that must exist under document root (public_html) for CSS/JS/images.
     */
    private const ASSET_DIRS = ['vendor', 'css', 'js', 'images'];

    private const ASSET_FILES = ['manifest.webmanifest', 'robots.txt', '.htaccess'];

    public static function detectPublicHtml(): ?string
    {
        $configured = env('PUBLIC_HTML_PATH');
        if (is_string($configured) && $configured !== '' && is_dir($configured)) {
            return rtrim($configured, '/\\');
        }

        $home = $_SERVER['HOME'] ?? null;
        if (! $home && function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_geteuid());
            $home = $info['dir'] ?? null;
        }

        if (! $home || ! is_dir($home.'/domains')) {
            return null;
        }

        $matches = glob($home.'/domains/*/public_html') ?: [];
        foreach ($matches as $dir) {
            if (is_dir($dir)) {
                return rtrim($dir, '/\\');
            }
        }

        return null;
    }

    public static function laravelRoot(): string
    {
        $root = env('BB_LARAVEL_ROOT');

        return (is_string($root) && $root !== '') ? rtrim($root, '/\\') : base_path();
    }

    /**
     * Sync Laravel public/ assets into Hostinger public_html and ensure index.php boots Laravel.
     */
    public static function sync(?string $publicHtml = null): array
    {
        $dest = $publicHtml ?? self::detectPublicHtml();
        if ($dest === null || ! is_dir($dest)) {
            return [
                'ok' => false,
                'message' => 'public_html not found. Set PUBLIC_HTML_PATH in .env (e.g. /home/u991240931/domains/behnabazar.in/public_html)',
            ];
        }

        $source = public_path();
        $laravelRoot = self::laravelRoot();
        $laravelPublic = $laravelRoot.DIRECTORY_SEPARATOR.'public';

        if (! is_dir($laravelPublic)) {
            return ['ok' => false, 'message' => 'Laravel public folder missing: '.$laravelPublic];
        }

        if (realpath($source) === realpath($dest)) {
            StoragePublicLink::ensure();

            return ['ok' => true, 'message' => 'Document root is already Laravel public/ — no split sync needed.', 'path' => $dest];
        }

        foreach (self::ASSET_DIRS as $dir) {
            $from = $source.DIRECTORY_SEPARATOR.$dir;
            if (! is_dir($from)) {
                continue;
            }
            $to = $dest.DIRECTORY_SEPARATOR.$dir;
            if (is_dir($to)) {
                File::deleteDirectory($to);
            }
            File::copyDirectory($from, $to);
        }

        foreach (self::ASSET_FILES as $file) {
            $from = $source.DIRECTORY_SEPARATOR.$file;
            if (is_file($from)) {
                File::copy($from, $dest.DIRECTORY_SEPARATOR.$file);
            }
        }

        self::writeBootstrapIndex($dest, $laravelPublic);
        self::syncStorageLink($dest, $source);

        $build = is_file($source.'/css/app.css')
            ? (string) filemtime($source.'/css/app.css')
            : 'unknown';

        return [
            'ok' => true,
            'message' => 'Synced CSS/JS/vendor/images to public_html and updated index.php bootstrap.',
            'path' => $dest,
            'build' => $build,
        ];
    }

    private static function writeBootstrapIndex(string $dest, string $laravelPublic): void
    {
        $laravelPublic = str_replace('\\', '/', $laravelPublic);
        $indexPath = $dest.DIRECTORY_SEPARATOR.'index.php';
        $marker = self::BOOTSTRAP_MARKER;
        $content = <<<PHP
<?php
/**
 * {$marker}
 * Document root: public_html — Laravel: {$laravelPublic}
 */
\$laravelPublic = '{$laravelPublic}';
if (! is_file(\$laravelPublic . '/index.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Behna Bazar: Laravel public/index.php not found at ' . \$laravelPublic;
    exit;
}
require \$laravelPublic . '/index.php';

PHP;
        File::put($indexPath, $content);
    }

    private static function syncStorageLink(string $dest, string $source): void
    {
        StoragePublicLink::ensure();
        PublicStorage::republishAll();
    }
}
