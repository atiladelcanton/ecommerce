<?php

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
        $middleware->validateCsrfTokens(except: [
            'webhooks/shipping/*',
            'webhooks/payments/*',
        ]);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin/*')
            ? route('filament.admin.auth.login')
            : route('customer.login'));
        $middleware->redirectUsersTo(fn ($request) => $request->is('admin/*')
            ? route('filament.admin.pages.dashboard')
            : route('customer.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Exception reporting customizations belong here.
    })
    ->create();
