<?php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'restrict.admin' => \App\Http\Middleware\RestrictAdminAccess::class, // tambahkan
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Penolakan akses (403) di halaman biasa tidak menampilkan layar error penuh,
        // tapi kembali ke halaman sebelumnya dengan pesan merah kecil (toast).
        // Request AJAX / JSON tetap dapat respons 403 seperti biasa.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403 || $request->expectsJson() || $request->ajax()) {
                return null;
            }

            $message = $e->getMessage() !== '' ? $e->getMessage() : "You don't have permission to do that.";

            // Kalau halaman sebelumnya sama dengan halaman yang ditolak (misalnya di-refresh),
            // arahkan ke dashboard supaya tidak muter-muter.
            $previous = url()->previous();
            $target = ($previous && $previous !== $request->fullUrl()) ? $previous : route('dashboard');

            return redirect($target)->with('access_error', $message);
        });
    })->create();
