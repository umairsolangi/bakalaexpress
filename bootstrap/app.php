<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureRiderIsApproved;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'approved_rider' => EnsureRiderIsApproved::class,
            'role' => RoleMiddleware::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'code' => 'UNAUTHENTICATED',
                    'errors' => (object) [],
                ], 401);
            }

            if ($e instanceof \Laravel\Sanctum\Exceptions\MissingAbilityException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                    'code' => 'FORBIDDEN_ROLE',
                    'errors' => (object) [],
                ], 403);
            }

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many attempts. Please try again later.',
                    'code' => 'TOO_MANY_ATTEMPTS',
                    'errors' => (object) [],
                ], 429);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'code' => 'NOT_FOUND',
                    'errors' => (object) [],
                ], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed.',
                    'code' => 'METHOD_NOT_ALLOWED',
                    'errors' => (object) [],
                ], 405);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $code = match ($status) {
                    401 => 'UNAUTHENTICATED',
                    403 => 'FORBIDDEN_ROLE',
                    404 => 'NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    422 => 'VALIDATION_ERROR',
                    429 => 'TOO_MANY_ATTEMPTS',
                    default => 'HTTP_ERROR',
                };

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP error.',
                    'code' => $code,
                    'errors' => (object) [],
                ], $status);
            }

            return response()->json([
                'success' => false,
                'message' => 'Server error.',
                'code' => 'SERVER_ERROR',
                'errors' => (object) [],
            ], 500);
        });
    })->create();
