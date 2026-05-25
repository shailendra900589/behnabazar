<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CaptureReferral::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrackVisitor::class,
        ]);
        $middleware->alias([
            'account.ready' => \App\Http\Middleware\EnsureAccountReady::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->expectsJson());

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (config('app.debug')) {
                return null;
            }

            if ($request->expectsJson()) {
                $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                    ? $e->getStatusCode()
                    : 500;

                return response()->json([
                    'message' => $status === 404 ? 'Not found.' : 'Something went wrong. Please try again.',
                ], ($status >= 400 && $status < 600) ? $status : 500);
            }

            return null;
        });
    })->create();
