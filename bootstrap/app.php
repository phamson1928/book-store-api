<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([

        ]);
        $middleware->group('api', [

        ]);
        $middleware->group('web', [

        ]);
        $middleware->alias([
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'checkAdmin' => \App\Http\Middleware\CheckAdmin::class,
        ]);
        
        // Cấu hình middleware auth để trả về JSON cho API
        $middleware->redirectTo(function (Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();
