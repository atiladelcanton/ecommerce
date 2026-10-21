<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Infrastructure\Catalog\CatalogHtmlExtractor;
use App\Infrastructure\Catalog\SafeCatalogHttp;
use RuntimeException;
use Tests\TestCase;

final class CatalogHtmlExtractorTest extends TestCase
{
    public function test_it_extracts_only_explicit_brl_price_and_product_fields(): void
    {
        $html = '<html><head><script type="application/ld+json">'
            .' {"@context":"https://schema.org", "@type":"Product", "name":"Tinta Teste",'
            .'"sku":"T-01", "description":"Uso profissional", "image":"https://cdn.exemplo.com/teste.jpg",'
            .'"offers":{"@type":"Offer","priceCurrency":"BRL","price":"19.90"}}'
            .'</script></head><body><a href="/produto/outro">Outro</a></body></html>';
        $extractor = new CatalogHtmlExtractor();
        $product = $extractor->product($html, 'https://loja.exemplo.com/produto/teste');
        $this->assertSame('Tinta Teste', $product['name']);
        $this->assertSame(1990, $product['price']);
        $this->assertSame('preco_minimo_confirmado', $product['price_status']);
        $this->assertSame(['https://cdn.exemplo.com/teste.jpg'], $product['images']);
        $this->assertContains('https://loja.exemplo.com/produto/outro', $extractor->links($html, 'https://loja.exemplo.com/')); 
    }

    public function test_private_hosts_are_rejected_before_http_requests(): void
    {
        $this->expectException(RuntimeException::class);
        app(SafeCatalogHttp::class)->assertPublicUrl('http://127.0.0.1/produto/teste');
    }
}
