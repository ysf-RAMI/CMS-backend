<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Unhandled exception: ' . $e->getMessage(), ['exception' => $e]);
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        Log::channel('single')->info('Handler: unauthenticated method called.');
        Log::channel('single')->info('Unauthenticated method called.', [
            'request_expects_json' => $request->expectsJson(),
            'request_is_api' => $request->is('api/*'),
            'request_path' => $request->path(),
            'exception_message' => $exception->getMessage()
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            Log::channel('single')->info('Returning 401 JSON response for unauthenticated request.');
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        Log::channel('single')->info('Redirecting unauthenticated request to login route.');
        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenExpiredException) {
            return response()->json(['message' => 'Token has expired'], 401);
        } else if ($exception instanceof TokenInvalidException) {
            return response()->json(['message' => 'Token is invalid'], 401);
        }

        return parent::render($request, $exception);
    }
}