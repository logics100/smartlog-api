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
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | SmartLog Web Authentication Redirects
        |--------------------------------------------------------------------------
        |
        | Guests are sent to the SmartLog login page.
        |
        | If Laravel's guest middleware detects an authenticated user,
        | send that request to the correct SmartLog dashboard instead of "/".
        |
        */

        $middleware->redirectGuestsTo(
            fn () => route('web.login')
        );

        $middleware->redirectUsersTo(function ($request) {

            $user = $request->user();

            if (!$user) {
                return route('web.login');
            }

            return match ($user->role) {
                'LECTURER' => route('web.lecturer.dashboard'),
                'HOD' => route('web.hod.dashboard'),
                'ICT_ADMIN' => route('web.admin.dashboard'),
                default => route('web.login'),
            };
        });

        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();