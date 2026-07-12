<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Quando o app roda sob um sub-path (ex.: http://localhost/SocialJobs/public),
        // o Livewire precisa saber onde servir o livewire.js e o endpoint /update.
        // Extraímos o path do APP_URL e prefixamos ambos.
        $appPath = trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($appPath !== '') {
            Livewire::setScriptRoute(function ($handle) use ($appPath) {
                return \Illuminate\Support\Facades\Route::get('/' . $appPath . '/livewire/livewire.js', $handle);
            });

            Livewire::setUpdateRoute(function ($handle) use ($appPath) {
                return \Illuminate\Support\Facades\Route::post('/' . $appPath . '/livewire/update', $handle);
            });
        }

        // Força o Laravel a gerar URLs com HTTPS quando estamos atrás de proxy TLS
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
