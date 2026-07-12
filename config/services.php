<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Groq — gera questões dos Skill Assessments em tempo real
    'groq' => [
        'key'      => env('GROQ_API_KEY'),
        'model'    => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'endpoint' => env('GROQ_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions'),
    ],

    // Oanor — moderação NSFW de imagens (bloqueia nudez em uploads)
    'oanor' => [
        'key'       => env('OANOR_API_KEY'),
        'endpoint'  => env('OANOR_ENDPOINT', 'https://api.oanor.com/nsfw-api/v1'),
        'threshold' => (float) env('OANOR_THRESHOLD', 0.6),
    ],

];
