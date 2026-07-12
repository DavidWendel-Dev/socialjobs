<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confia no proxy reverso (Cloudflare + Nginx interno do Docker).
        // Sem isso, request()->isSecure() retorna false em produção HTTPS via
        // Cloudflare Flexible, e cookies com Secure=true não persistem,
        // quebrando sessão / Livewire / uploads.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // Headers de segurança em todas respostas
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Minifica HTML em produção (skip Livewire/JSON automaticamente)
        $middleware->append(\App\Http\Middleware\MinifyHtml::class);

        // Content-Security-Policy via Spatie
        if (class_exists(\Spatie\Csp\AddCspHeaders::class)) {
            $middleware->web(append: [\Spatie\Csp\AddCspHeaders::class]);
        }

        // Aliases de middleware customizados
        $middleware->alias([
            'user.type' => \App\Http\Middleware\EnsureUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booted(function () {
        // Rate limiters nomeados
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(3)->by($r->ip()));
        RateLimiter::for('ai', fn (Request $r) => Limit::perMinute(20)->by(optional($r->user())->id ?: $r->ip()));
        RateLimiter::for('posts', fn (Request $r) => Limit::perMinute(10)->by(optional($r->user())->id ?: $r->ip()));
        RateLimiter::for('reactions', fn (Request $r) => Limit::perMinute(60)->by(optional($r->user())->id ?: $r->ip()));
    })
    ->create();
