<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->ajax() && $request->isMethod('GET') && !str_starts_with($request->path(), 'api/')) {
            try {
                DB::table('site_visits')->where('id', 1)->increment('total_count');
            } catch (\Throwable $e) {
                // Table may not exist yet
            }
        }

        return $next($request);
    }
}
