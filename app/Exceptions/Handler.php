<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     */
    protected $levels = [];

    /**
     * A list of exception types that are not reported.
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render exceptions into HTTP responses.
     */
    public function render($request, Throwable $e)
    {
        // 🔐 SESSION HABIS / BELUM LOGIN
        if ($e instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            return redirect()->route('login');
        }

        // ⏳ CSRF EXPIRED (419)
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired'
                ], 419);
            }

            return redirect()->route('login');
        }

        return parent::render($request, $e);
    }
}
