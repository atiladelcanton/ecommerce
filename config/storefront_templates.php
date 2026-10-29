<?php

declare(strict_types=1);

return [
    // A configuração antiga do .env continua como fallback até o primeiro uso do seletor.
    'active' => env('STOREFRONT_TEMPLATE', '1'),
    'available' => [
        1 => 'Tema padrão',
        2 => 'Mercatto',
        3 => 'Horizonte',
        4 => 'Essência',
        5 => 'Pulso',
    ],
    'descriptions' => [
        1 => 'Visual original, com foco em catálogo e navegação direta.',
        2 => 'Vitrine comercial com departamentos e busca em destaque.',
        3 => 'Loja moderna com campanha ampla e navegação clássica.',
        4 => 'Composição editorial minimalista e produtos em primeiro plano.',
        5 => 'Estilo urbano de alto contraste e comunicação impactante.',
    ],
];
