<?php

declare(strict_types=1);

return [
    'groq' => [
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'models' => [
            'openai/gpt-oss-120b' => 'GPT-OSS 120B · melhor qualidade',
            'openai/gpt-oss-20b' => 'GPT-OSS 20B · mais rápido',
        ],
    ],
];
