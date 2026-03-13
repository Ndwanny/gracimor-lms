<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceJsonResponse;
use App\Providers\EventServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))

    // ── Route files ───────────────────────────────────────────────────────────
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        apiPrefix: 'api',
    )

    // ── Middleware ────────────────────────────────────────────────────────────
    ->withMiddleware(function (Middleware $middleware) {

        // Force JSON responses on API routes only (not Blade web pages)
        $middleware->api(append: [ForceJsonResponse::class]);

        // Register named middleware aliases
        $middleware->alias([
            'role'        => CheckRole::class,
            'active.user' => EnsureUserIsActive::class,
        ]);

        // Token-based auth (Bearer) — statefulApi() not needed
    })

    // ── Providers ─────────────────────────────────────────────────────────────
    ->withProviders([
        EventServiceProvider::class,
    ])

    // ── Exception handling ────────────────────────────────────────────────────
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\Throwable $e, Request $request) {

            // For web routes, let Laravel handle errors normally (HTML error pages)
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            // 404 — model not found (route model binding)
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }

            // 405 — wrong HTTP method
            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'message' => 'Method not allowed.',
                ], 405);
            }

            // 422 — validation failed
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // 401 — unauthenticated
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // 403 — authorisation
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            // Production: swallow stack traces
            if (app()->environment('production')) {
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again.',
                ], 500);
            }

            // Development: expose the full message and trace
            return response()->json([
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect($e->getTrace())->take(10)->toArray(),
            ], 500);
        });
    })

    ->create();
