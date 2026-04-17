<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): \Illuminate\Http\Response|JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($e instanceof UnauthorizedException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }

            if ($e instanceof ModelNotFoundException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'The requested resource was not found.',
                ], 404);
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'Method not allowed for this route.',
                ], 405);
            }

            if ($e instanceof NotFoundHttpException) {
                return new JsonResponse([
                    'status'  => false,
                    'message' => 'The requested URL was not found.',
                ], 404);
            }

            if ($e instanceof HttpException) {
                // Only expose the message for safe 4xx codes; never for 5xx
                $statusCode = $e->getStatusCode();
                $message    = $statusCode < 500 && $e->getMessage()
                    ? $e->getMessage()
                    : 'An unexpected error occurred.';

                return new JsonResponse([
                    'status'  => false,
                    'message' => $message,
                ], $statusCode ?: 422);
            }

            if ($e instanceof QueryException) {
                // Log full details server-side; never expose SQL in the response
                \Illuminate\Support\Facades\Log::error('QueryException', [
                    'message' => $e->getMessage(),
                    'path'    => $request->path(),
                ]);

                return new JsonResponse([
                    'status'  => false,
                    'message' => 'A database error occurred. Please try again.',
                ], 500);
            }

            // Generic catch-all — log and return a safe message
            \Illuminate\Support\Facades\Log::error('Unhandled API exception', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'path'      => $request->path(),
            ]);

            return new JsonResponse([
                'status'  => false,
                'message' => app()->isProduction()
                    ? 'An unexpected error occurred.'
                    : $e->getMessage(),
            ], 500);
        }

        return parent::render($request, $e);
    }
}
