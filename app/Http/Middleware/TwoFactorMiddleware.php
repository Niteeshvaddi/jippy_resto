<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // If user has 2FA enabled but hasn't verified in this session
        if ($user->two_factor_enabled && !session('2fa_verified')) {
            // Don't redirect if already on 2FA verification page
            if (!$request->routeIs('2fa.*')) {
                session(['2fa_required' => true]);
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }
}
