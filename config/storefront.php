<?php

declare(strict_types=1);

return [
    'order_prefix' => env('ORDER_PREFIX', 'SABA'),
    // Editorial content is separate from presentation; a future admin source can replace this configuration.
    'about' => [
        'title' => 'Quem Somos',
        'introduction' => 'Conheça nosso catálogo e encontre produtos, variações e condições de compra em um só lugar.',
        'sections' => [
            [
                'title' => 'Produtos para diferentes necessidades',
                'text' => 'Explore o catálogo por categorias e encontre a opção adequada para cada necessidade.',
            ],
            [
                'title' => 'Atendimento especializado',
                'text' => 'Converse com a equipe sobre os produtos do catálogo e tire suas dúvidas antes de escolher.',
            ],
            [
                'title' => 'Escolhas para diferentes necessidades',
                'text' => 'Cada produto pode ter opções próprias de cor, tamanho, medida, embalagem ou outras características. Escolha a combinação que faz sentido para você.',
            ],
        ],
    ],
];
