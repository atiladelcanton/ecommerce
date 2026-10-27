<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog;

use App\Infrastructure\Catalog\CompleteProductVariationExtractor;
use PHPUnit\Framework\TestCase;

final class CompleteProductVariationExtractorTest extends TestCase
{
    public function test_empty_optional_attributes_do_not_crash_pinus_variations(): void
    {
        $html = <<<'HTML'
<html><body>
<div class="prod-option option-input" data-attribute-name="" data-items=""></div>
<div class="prod-option option-input" data-attribute-name="" data-items="ignorar"></div>
<div class="prod-option option-input option-Tamanho" data-attribute-name="Tamanho"
 data-prod-option="" data-attribute="2" data-qtd="8"
 data-items="20x25 // 20x30 // 20x40 // 25x35 // 30x40 // 40x50 // 50x60 // 60x70"></div>
</body></html>
HTML;
        $result = (new CompleteProductVariationExtractor)->extractDetailed(
            $html,
            'https://www.telesilkonline.com.br/produto/quadro-de-madeira-pinus-medida-externa-177'
        );

        $this->assertCount(8, $result['options']);
        $this->assertCount(8, $result['variants']);
        $this->assertSame(['Tamanho' => '20x25'], $result['variants'][0]);
        $this->assertSame(['Tamanho' => '60x70'], $result['variants'][7]);
        $this->assertSame([], $result['warnings']);
    }

    public function test_accepts_single_quoted_and_unquoted_html_attributes(): void
    {
        $html = <<<'HTML'
<html><body>
<div class=prod-option data-attribute-name=Tamanho data-items='20x25 // 30x40'></div>
</body></html>
HTML;
        $result = (new CompleteProductVariationExtractor)->extractDetailed(
            $html,
            'https://www.telesilkonline.com.br/produto/quadro-de-madeira-pinus-medida-externa-177'
        );

        $this->assertSame([
            ['Tamanho' => '20x25'],
            ['Tamanho' => '30x40'],
        ], $result['variants']);
    }

    public function test_multiple_independent_attributes_do_not_create_combinations(): void
    {
        $html = <<<'HTML'
<html><body>
<div class="prod-option" data-attribute-name="Tamanho" data-items="20x25 // 30x40"></div>
<div class="prod-option" data-attribute-name="Cor" data-items="Branco // Preto"></div>
</body></html>
HTML;
        $result = (new CompleteProductVariationExtractor)->extractDetailed($html, 'https://www.telesilkonline.com.br/produto/exemplo-177');

        $this->assertCount(4, $result['options']);
        $this->assertSame([], $result['variants']);
        $this->assertNotEmpty($result['warnings']);
    }
}
