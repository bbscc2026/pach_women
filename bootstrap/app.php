<?php

use App\Http\Middleware\ClearSiteDataOnLogout;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Razorpay's webhook is verified by its signature instead.
        $middleware->preventRequestForgery(except: ['payment/razorpay/webhook']);

        // Wipe offline copies of pages from the phone when someone logs out.
        $middleware->web(append: [ClearSiteDataOnLogout::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
