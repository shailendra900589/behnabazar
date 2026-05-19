<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class BbAsset
{
    /**
     * Build a public asset URL from the current request host (works when APP_URL is still localhost on live).
     */
    public static function url(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (app()->runningInConsole()) {
            return asset($path);
        }

        $request = Request::instance();
        $root = rtrim($request->getSchemeAndHttpHost().$request->getBasePath(), '/');

        return $root.'/'.$path;
    }
}
