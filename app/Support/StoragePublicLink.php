<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class StoragePublicLink
{
    /**
     * Ensure public/storage points at storage/app/public without using exec() (often disabled on shared hosting).
     */
    public static function ensure(): bool
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        File::ensureDirectoryExists($target);

        if (self::linkIsValid($link, $target)) {
            return true;
        }

        if (is_link($link) || file_exists($link)) {
            @unlink($link);
            @rmdir($link);
        }

        if (function_exists('symlink')) {
            try {
                if (@symlink($target, $link)) {
                    return true;
                }
            } catch (\Throwable) {
                // try next method
            }
        }

        if (windows_os() && self::canRunExec()) {
            $targetWin = str_replace('/', '\\', $target);
            $linkWin = str_replace('/', '\\', $link);
            $cmd = 'cmd /C mklink /J '.escapeshellarg($linkWin).' '.escapeshellarg($targetWin);
            @exec($cmd, $output, $code);

            if ($code === 0 && self::linkIsValid($link, $target)) {
                return true;
            }
        }

        if (! windows_os() && self::canRunExec() && function_exists('symlink') === false) {
            $cmd = 'ln -s '.escapeshellarg($target).' '.escapeshellarg($link);
            @exec($cmd, $output, $code);

            if ($code === 0 && self::linkIsValid($link, $target)) {
                return true;
            }
        }

        return self::mirrorDirectory($target, $link);
    }

    public static function helpMessage(): string
    {
        return 'public/storage could not be symlinked (exec/symlink disabled). A folder copy was used if possible — for production, enable symlink or map public/storage to storage/app/public in your panel.';
    }

    private static function linkIsValid(string $link, string $target): bool
    {
        if (! file_exists($link) && ! is_link($link)) {
            return false;
        }

        $resolved = realpath($link);

        return $resolved !== false && realpath($target) === $resolved;
    }

    private static function canRunExec(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $disabled = ini_get('disable_functions');
        if ($disabled === false || $disabled === '') {
            return true;
        }

        $list = array_map('trim', explode(',', strtolower($disabled)));

        return ! in_array('exec', $list, true);
    }

    /** Fallback when symlinks are impossible: copy tree (uploads still work). */
    private static function mirrorDirectory(string $source, string $dest): bool
    {
        try {
            if (is_dir($dest)) {
                File::deleteDirectory($dest);
            }
            File::copyDirectory($source, $dest);

            return is_dir($dest);
        } catch (\Throwable) {
            return false;
        }
    }
}
