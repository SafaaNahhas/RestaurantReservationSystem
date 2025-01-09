<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
        if ($request->expectsJson()) {
            if ($exception instanceof UnauthorizedHttpException) {
                return response()->json([
                    'message' => 'User does not have the right roles.'
                ], 403); // 403 Forbidden
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }

            if ($exception instanceof RelationNotFoundException) {
                return response()->json([
                    'message' => 'The specified relationship does not exist.'
                ], 404);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'The page you are looking for was not found.',
                ], 404);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'details' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof BadRequestHttpException) {
                return response()->json([
                    'message' => 'Bad Request Error.',
                    'details' => $exception->getMessage()
                ], 400);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'error' => 'Unauthenticated.',
                    'message' => 'You need to log in to access this resource.'

                ], 401);
            }

            if ($exception instanceof JWTException) {
                return response()->json([
                    'message' => 'Token error: ' . $exception->getMessage()
                ], 401);
            }

            if ($exception instanceof AccessDeniedHttpException) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.'
                ], 403);
            }

            Log::error('API Exception: ' . $exception->getMessage(), [
                'exception' => $exception,
                'url' => $request->fullUrl(),
                'input' => $request->all(),
            ]);

            $status = 500;
            if (method_exists($exception, 'getStatusCode')) {
                $status = $exception->getStatusCode();
            }

            $message = $exception->getMessage() ?: 'An unexpected error occurred.';

            return response()->json([
                'error' => $message
            ], $status);

        }

        return parent::render($request, $exception);
    }
}

