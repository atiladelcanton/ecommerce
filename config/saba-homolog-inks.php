<?php

declare(strict_types=1);

// A URL do fornecedor é usada APENAS para leitura, sem publicação na vitrine.
// As opções foram conferidas nas páginas em 23/10/2026.
// A disponibilidade comercial das combinações (cor/embalagem) não foi confirmada.
return [
    'host' => 'https://www.telesilkonline.com.br',
    'products' => [
        [
            'number' => '276', 'slug' => 'plastisol-stamp-super-opaco-276',
            'name' => 'Plastisol Stamp Super Opaco', 'category' => 'Tinta Plastisol', 'code' => 'TP',
            'summary' => 'Tinta plastisol de alta opacidade para serigrafia em tecidos; requer cura térmica.',
            'path' => '/produto/plastisol-stamp-super-opaco-276',
            'colors' => ['Amarelo limão 003', 'Amarelo ouro 002', 'Azul médio 015', 'Azul ultramar 017', 'Bordô 014', 'Branco super opaco 101', 'Incolor mix', 'Preto 025', 'Verde bandeira 020', 'Vermelho vivo 010', 'Violeta 030'],
            'packages' => ['5kg', '900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2025/09/30/16_53_03_244_08d2fd3b03122923eff17695892fd58e.jpg?v=1761247206',
        ],
        [
            'number' => '243', 'slug' => 'tinta-hidrocryl-cromia-243',
            'name' => 'Tinta Hidrocryl Cromia', 'category' => 'Tinta Base Água', 'code' => 'TA',
            'summary' => 'Tinta serigráfica de base aquosa para impressões em quadricromia CMYK sobre tecidos claros.',
            'path' => '/produto/tinta-hidrocryl-cromia-243',
            'colors' => ['Amarelo 150', 'Cyan 152', 'Magenta 151', 'Preto 153'],
            'packages' => ['900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2026/02/11/08_23_17_561_hidrocryl-cromia-08023060.jpg?v=1770808999',
        ],
        [
            'number' => '249', 'slug' => 'tinta-hidrocryl-perolizado-249',
            'name' => 'Tinta Hidrocryl Perolizado', 'category' => 'Tinta Base Água', 'code' => 'TA',
            'summary' => 'Tinta à base de água para serigrafia em tecidos com efeito perolado de brilho suave.',
            'path' => '/produto/tinta-hidrocryl-perolizado-249',
            'colors' => ['Ouro rico 081', 'Prata 033'],
            'packages' => ['250ml', '900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2026/08/25/09_25_44_684_hidrocryl-perolizado-250-09084580.jpg?v=1787660747',
        ],
        [
            'number' => '250', 'slug' => 'tinta-hidrocryl-prime-soft-toque-900ml-250',
            'name' => 'Tinta Hidrocryl Prime Soft Toque 900ml', 'category' => 'Tinta Base Água', 'code' => 'TA',
            'summary' => 'Acabamento à base de água para proporcionar toque macio e levemente aveludado às estampas.',
            'path' => '/produto/tinta-hidrocryl-prime-soft-toque-900ml-250',
            'colors' => [], 'packages' => ['900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2025/11/08/11_07_34_427_hidrocryl-prime-soft-900ml-11116870.jpg?v=1762610854',
        ],
        [
            'number' => '264', 'slug' => 'plastisol-free-super-macio-264',
            'name' => 'Plastisol Free Super Macio', 'category' => 'Tinta Plastisol', 'code' => 'TP',
            'summary' => 'Tinta plastisol de toque macio para serigrafia; requer equipamento de cura térmica.',
            'path' => '/produto/plastisol-free-super-macio-264',
            'colors' => ['Branco 001', 'Incolor 000'], 'packages' => ['900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2026/01/16/10_05_51_559_tinta-plastisol-free-super-macio-10014190.jpg?v=1768568754',
        ],
        [
            'number' => '273', 'slug' => 'tinta-plastisol-stamp-soft-fluor-273',
            'name' => 'Tinta Plastisol Stamp Soft Fluor', 'category' => 'Tinta Plastisol', 'code' => 'TP',
            'summary' => 'Tinta plastisol fluorescente para efeitos visuais intensos em serigrafia; exige cura térmica.',
            'path' => '/produto/tinta-plastisol-stamp-soft-fluor-273',
            'colors' => ['Amarelo limão fluorescente 070', 'Laranja fluorescente 072', 'Pink (maravilha) fluorescente 074', 'Verde fluorescente 075'],
            'packages' => ['900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2026/01/16/10_19_06_382_tinta-plastisol-stamp-soft-fluor-10017190.jpg?v=1768569549',
        ],
        [
            'number' => '270', 'slug' => 'tinta-plastisol-relevo-base-270',
            'name' => 'Tinta Plastisol Relevo Base', 'category' => 'Tinta Plastisol', 'code' => 'TP',
            'summary' => 'Plastisol para serigrafia com efeito de relevo e necessidade de cura térmica.',
            'path' => '/produto/tinta-plastisol-relevo-base-270',
            // No HTML de origem os rótulos Cor/Tamanho aparecem trocados; valores corrigidos abaixo.
            'colors' => ['Amarelo limão 003', 'Amarelo limão fluorescente 070', 'Amarelo ouro 002', 'Azul marinho 016', 'Azul médio 015', 'Branco 001', 'Cinza', 'Incolor clear 000', 'Laranja fluorescente 072', 'Pink (maravilha) fluorescente 074', 'Preto 025', 'Verde bandeira 020', 'Verde fluorescente 075', 'Vermelho vivo 010', 'Violeta 030'],
            'packages' => ['900ml'],
            'fallback_image' => 'https://cdn.vnda.com.br/150x/telesilkonline/2026/01/16/10_08_37_120_tinta-plastisol-relevo-base-10019520.jpg?v=1768568921',
        ],
    ],
];
