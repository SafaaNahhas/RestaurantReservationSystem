<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register(): void {}

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage() ?: "You don't have the required role or permission.",
            ], 403);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'error' => 'You need to be logged in to access this resource.',
                'code' => 401
            ], 401);
        }

        if ($exception instanceof \Exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage() ?? "An unexpected error occurred.",
            ], 500);

        }

        // if ($exception instanceof \Exception) {
        //     return response()->json([
        //         'error' => true,
        //         'message' => "An unexpected error occurred.",
        //     ], 500);
        // }
        if ($exception instanceof \Exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage() ?? "An unexpected error occurred.",
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
