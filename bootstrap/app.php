<?php

use App\Http\Middleware\EnsureOrganizationAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTenantPortalAccess;
use App\Http\Middleware\ResolveAuthenticatedTenancy;
use App\Http\Middleware\ResolveOrganizationContext;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'org.context' => ResolveOrganizationContext::class,
            'org.access' => EnsureOrganizationAccess::class,
            'super_admin' => EnsureSuperAdmin::class,
            'tenant.portal' => EnsureTenantPortalAccess::class,
            'auth.tenancy' => ResolveAuthenticatedTenancy::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('dormai:expire-reservations')->hourly();
        $schedule->command('dormai:mark-overdue-invoices')->daily();
        $schedule->command('dormai:generate-monthly-invoices')->monthlyOn(1, '02:00');
        $schedule->command('dormai:send-payment-reminders')->dailyAt('08:00');
        $schedule->command('dormai:send-overdue-notifications')->dailyAt('09:00');
        $schedule->command('dormai:send-contract-expiration-reminders')->dailyAt('08:30');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
