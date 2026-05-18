<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountReady
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->role === 'user' && ! $user->is_email_verified) {
            return redirect()->route('account.verify.show')
                ->with('warning', 'Please verify your email to continue.');
        }

        if ($user->role === 'vendor') {
            if ($user->account_status === 'rejected') {
                auth()->logout();
                $request->session()->invalidate();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Your vendor application was rejected. Contact support for details.']);
            }

            if ($user->account_status === 'pending_payment') {
                return redirect()->route('vendor.payment.show')
                    ->with('warning', 'Complete your registration fee to continue.');
            }
        }

        return $next($request);
    }
}
