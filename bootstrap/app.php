<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Domain\Exception\DomainException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. Business exceptions (DomainException: 401, 404, 409 etc.)
        $exceptions->renderable(function (DomainException $e, $request) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getStatusCode());
        });

        // 2. Native validation exceptions (e.g. FormRequest, validate())
        $exceptions->renderable(function (ValidationException $e, $request) {
            $errorMessage = $e->validator->errors()->first() ?: 'Validation failed.';
            return response()->json([
                'error' => $errorMessage
            ], 400);
        });

        // 3. HTTP exceptions (e.g. 404 Not Found, 403 Forbidden, 405 Method Not Allowed)
        $exceptions->renderable(function (HttpExceptionInterface $e, $request) {
            return response()->json([
                'error' => $e->getMessage() ?: 'Http Error'
            ], $e->getStatusCode());
        });

        // 4. Authentication exceptions (401)
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            return response()->json([
                'error' => 'Unauthenticated.'
            ], 401);
        });

        // 5. All other unhandled exceptions (500)
        $exceptions->renderable(function (\Throwable $e, $request) {
            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        });
    })->create();