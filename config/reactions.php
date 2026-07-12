<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reações estilo Facebook
    |--------------------------------------------------------------------------
    | Ordem importa — é a ordem em que aparecem no arco animado ao passar o mouse.
    */

    'types' => [
        'like' => [
            'label' => 'Curtir',
            'emoji' => '👍',
            'color' => '#1877F2',
        ],
        'love' => [
            'label' => 'Amei',
            'emoji' => '❤️',
            'color' => '#F43F5E',
        ],
        'celebrate' => [
            'label' => 'Parabéns',
            'emoji' => '🎉',
            'color' => '#F59E0B',
        ],
        'support' => [
            'label' => 'Apoio',
            'emoji' => '🤝',
            'color' => '#F97316',
        ],
        'insightful' => [
            'label' => 'Perspicaz',
            'emoji' => '💡',
            'color' => '#8B5CF6',
        ],
        'funny' => [
            'label' => 'Engraçado',
            'emoji' => '😂',
            'color' => '#EAB308',
        ],
    ],

    'default' => 'like',
];
