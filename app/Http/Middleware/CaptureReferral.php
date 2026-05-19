<?php

namespace App\Http\Middleware;

use App\Services\ReferralProgramService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('ref')) {
            app(ReferralProgramService::class)->captureReferralFromRequest($request->query('ref'));
        }

        return $next($request);
    }
}
