<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sistema de Pontos, Níveis e Ranking do SocialJobs
    |--------------------------------------------------------------------------
    | Cada ação define quanto XP concede, com limites diários para evitar abuso.
    */

    'actions' => [
        // action_key => [xp, daily_limit (null = ilimitado), once_only]
        'profile.completed'          => ['xp' => 100, 'daily_limit' => null, 'once' => true],
        'email.verified'             => ['xp' => 20,  'daily_limit' => null, 'once' => true],
        '2fa.enabled'                => ['xp' => 30,  'daily_limit' => null, 'once' => true],
        'post.first'                 => ['xp' => 30,  'daily_limit' => null, 'once' => true],
        'post.created'               => ['xp' => 5,   'daily_limit' => 3,    'once' => false],
        'reaction.received'          => ['xp' => 1,   'daily_limit' => 50,   'once' => false],
        'comment.created'            => ['xp' => 3,   'daily_limit' => 10,   'once' => false],
        'follower.gained'            => ['xp' => 5,   'daily_limit' => 20,   'once' => false],
        'endorsement.given'          => ['xp' => 2,   'daily_limit' => 10,   'once' => false],
        'endorsement.received'       => ['xp' => 5,   'daily_limit' => 20,   'once' => false],
        'recommendation.received'    => ['xp' => 30,  'daily_limit' => null, 'once' => false],
        'application.sent'           => ['xp' => 10,  'daily_limit' => 5,    'once' => false],
        'application.hired'          => ['xp' => 500, 'daily_limit' => null, 'once' => false],
        'lesson.completed'           => ['xp' => 15,  'daily_limit' => null, 'once' => false],
        'module.passed'              => ['xp' => 50,  'daily_limit' => null, 'once' => false],
        'course.completed'           => ['xp' => 200, 'daily_limit' => null, 'once' => false],
        'skill.passed'               => ['xp' => 150, 'daily_limit' => null, 'once' => false],
        'interview.simulated'        => ['xp' => 25,  'daily_limit' => 3,    'once' => false],
        'interview.high_score'       => ['xp' => 75,  'daily_limit' => null, 'once' => false],
        'login.daily'                => ['xp' => 5,   'daily_limit' => 1,    'once' => false],
        'login.streak_week'          => ['xp' => 5,   'daily_limit' => null, 'once' => false],
        'report.validated'           => ['xp' => 10,  'daily_limit' => null, 'once' => false],
    ],

    /*
    |--------------------------------------------------------------------------
    | Faixas de nível — [XP mínimo => ['level' => n, 'name' => 'Nome']]
    |--------------------------------------------------------------------------
    */
    'levels' => [
        0     => ['level' => 1, 'name' => 'Explorador',  'ring_color' => '#94A3B8'],
        200   => ['level' => 2, 'name' => 'Conectado',   'ring_color' => '#22C55E'],
        600   => ['level' => 3, 'name' => 'Ativo',       'ring_color' => '#0EA5E9'],
        1500  => ['level' => 4, 'name' => 'Referência',  'ring_color' => '#8B5CF6'],
        4000  => ['level' => 5, 'name' => 'Mentor',      'ring_color' => '#F97316'],
        10000 => ['level' => 6, 'name' => 'Lenda',       'ring_color' => '#F59E0B'],
    ],
];
