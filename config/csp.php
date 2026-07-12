<?php

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;

return [

    /*
     * Presets que definirão quais cabeçalhos CSP serão adicionados.
     */
    'presets' => [
        Spatie\Csp\Presets\Basic::class,
    ],

    /**
     * Diretivas CSP globais adicionais.
     * OBS: em desenvolvimento (APP_ENV=local) liberamos algumas origens
     * comuns (Vite HMR, fontes de vendors). Em produção reduza.
     */
    'directives' => [
        [Directive::FRAME, ['https://www.youtube.com', 'https://player.vimeo.com', 'https://www.youtube-nocookie.com']],
        [Directive::IMG, [Keyword::SELF, 'data:', 'blob:', 'https:']],
        // Fontes: liberamos qualquer origem HTTPS + data: para não brigar com libs
        // de terceiros que embutem @font-face (ex.: Zoho, Bunny, Cloud Typography).
        [Directive::FONT, [Keyword::SELF, 'https:', 'data:']],
        [Directive::STYLE, [Keyword::SELF, Keyword::UNSAFE_INLINE, 'https:']],
        [Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_INLINE, Keyword::UNSAFE_EVAL, 'https:']],
        // WebSocket (Reverb) + Vite HMR
        [Directive::CONNECT, [Keyword::SELF, 'ws:', 'wss:', 'http://localhost:*', 'https:']],
        [Directive::MEDIA, [Keyword::SELF, 'data:', 'blob:', 'https:']],
    ],

    'report_only_presets' => [
        //
    ],

    'report_only_directives' => [
        //
    ],

    'report_uri' => env('CSP_REPORT_URI', ''),
    'report_only_uri' => env('CSP_REPORT_ONLY_URI', ''),
    'report_to' => env('CSP_REPORT_TO', ''),
    'report_only_to' => env('CSP_REPORT_ONLY_TO', ''),

    'reporting_endpoints' => [
        //
    ],

    'enabled' => env('CSP_ENABLED', true),
    'enabled_while_hot_reloading' => env('CSP_ENABLED_WHILE_HOT_RELOADING', false),
    'nonce_generator' => Spatie\Csp\Nonce\RandomString::class,
    'nonce_enabled' => env('CSP_NONCE_ENABLED', false),
];
