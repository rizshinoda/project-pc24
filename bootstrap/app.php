<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\GAMiddleware;
use App\Http\Middleware\HelpdeskMiddleware;
use App\Http\Middleware\NAMiddleware;
use App\Http\Middleware\NOCMiddleware;
use App\Http\Middleware\PSBMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',

        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'superadmin' => SuperAdminMiddleware::class,
            'admin' => AdminMiddleware::class,
            'helpdesk' => HelpdeskMiddleware::class,
            'ga' => GAMiddleware::class,
            'noc' => NOCMiddleware::class,
            'psb' => PSBMiddleware::class,
            'na' => NAMiddleware::class

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
