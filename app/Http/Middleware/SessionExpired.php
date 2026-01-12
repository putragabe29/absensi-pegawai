<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionExpired
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {

            // WebView / AJAX
            if ($request->expectsJson() || str_contains($request->userAgent(), 'Android')) {
                return response()->json([
                    'session_expired' => true,
                    'redirect' => route('login')
                ], 401);
            }

            // Browser
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah habis, silakan login ulang.');
        }

        return $next($request);
    }
}
