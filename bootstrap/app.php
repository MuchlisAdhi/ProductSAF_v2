<?php

use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\ManualMaintenanceMode;
use App\Http\Middleware\TrackPublicVisit;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestsDuringMaintenance(except: [
            'admin*',
            'login',
            'logout',
            'up',
        ]);

        $middleware->web(prepend: [
            ManualMaintenanceMode::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'api.auth' => EnsureApiAuthenticated::class,
            'role' => EnsureRole::class,
            'track.public' => TrackPublicVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
