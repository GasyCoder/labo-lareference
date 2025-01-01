<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configuration Livewire
        $middleware->validateCsrfTokens(except: [
            'livewire/*',
            'livewire/message/*',
            'livewire/upload-file',
            'livewire/preview-file'
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion spécifique de l'erreur 500
        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof HttpException && $e->getStatusCode() === 500) {
                return redirect()->route('login');
            }

            if ($e instanceof \Error || $e instanceof \Exception) {
                return redirect()->route('login');
            }

            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login');
            }
        });

        // Logging simplifié des erreurs
        $exceptions->report(function (\Throwable $e) {
            \Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        });
    })
    ->create();
