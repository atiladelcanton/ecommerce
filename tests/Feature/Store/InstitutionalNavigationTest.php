<?php

declare(strict_types=1);

namespace Tests\Feature\Store;

use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Settings\Models\StoreSetting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class InstitutionalNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function xpath(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    public static function publicPages(): array
    {
        return [
            'about' => ['/quem-somos', 'Quem Somos', 'store.about'],
            'contact' => ['/contato', 'Contato', 'store.contact'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_institutional_pages_are_public_and_mark_current_navigation(string $path, string $title, string $route): void
    {
        $response = $this->get($path)->assertOk()->assertSee($title);
        $xpath = $this->xpath($response->getContent());
        $this->assertSame($title, $xpath->query('//main//h1')->item(0)->textContent);
        $this->assertSame(1, $xpath->query('//header//nav[@aria-label="Navegação principal"]')->length);
        $this->assertSame(2, $xpath->query('//header//a[@href="'.route($route).'" and @aria-current="page"]')->length);
        $this->assertDatabaseCount('store_settings', 0);
    }

    public function test_shared_header_has_correct_desktop_and_mobile_links_and_existing_shortcuts(): void
    {
        $xpath = $this->xpath($this->get('/quem-somos')->assertOk()->getContent());
        foreach (['Navegação principal', 'Navegação mobile'] as $navigation) {
            foreach (['home', 'store.products', 'store.about', 'store.contact'] as $route) {
                $this->assertGreaterThanOrEqual(1, $xpath->query('//header//nav[@aria-label="'.$navigation.'"]//a[@href="'.route($route).'"]')->length);
            }
        }
        $this->assertSame(1, $xpath->query('//*[@id="category-mega-menu"]//a[@href="'.route('store.products').'"]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="category-mega-menu"]//a[@href="'.route('store.products', ['promocao' => 1]).'"]')->length);
        $this->assertSame(0, $xpath->query('//header//a[contains(@href,"destaque") or contains(@href,"carrinho") or contains(@href,"conta/")]')->length);
        $this->assertSame(1, $xpath->query('//header//form[@role="search" and @action="'.route('store.products').'"]')->length);
        $this->assertSame(1, $xpath->query('//header//*[@id="store-search"]')->length);
    }

    public function test_menu_uses_only_active_database_categories_in_order(): void
    {
        $later = Category::factory()->create(['name' => 'Categoria real B', 'sort_order' => 10]);
        $first = Category::factory()->create(['name' => 'Categoria real A', 'sort_order' => 1]);
        $inactive = Category::factory()->create(['name' => 'Categoria inativa', 'is_active' => false]);
        $response = $this->get('/quem-somos')->assertOk()->assertDontSee($inactive->name);
        $xpath = $this->xpath($response->getContent());
        foreach (['//*[@id="category-mega-menu"]//ul/li/a', '//header//details//ul/li/a'] as $selector) {
            $links = $xpath->query($selector);
            $this->assertSame(2, $links->length);
            foreach ([$first, $later] as $index => $category) {
                $this->assertSame($category->name, $links->item($index)->textContent);
                $this->assertSame(route('store.products', ['categoria' => $category->slug]), $links->item($index)->getAttribute('href'));
            }
        }
    }

    public function test_contact_uses_configured_channels_and_full_address(): void
    {
        app(UpdateStoreSettingsAction::class)->execute([
            'store_name' => 'SABA', 'phone' => '+55 (11) 3333-2222', 'whatsapp' => '+55 (11) 99999-2222',
            'email' => 'atendimento@example.com', 'street' => 'Rua de Teste', 'number' => '0',
            'complement' => 'Sala 2', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP', 'zip_code' => '13000-000',
        ]);
        $response = $this->get('/contato')->assertOk()
            ->assertViewHas('addressLines', ['Rua de Teste, 0', 'Sala 2', 'Centro', 'Campinas / SP', 'CEP 13000-000']);
        $xpath = $this->xpath($response->getContent());
        foreach (['tel:+551133332222', 'https://wa.me/5511999992222', 'mailto:atendimento@example.com'] as $url) {
            $this->assertSame(1, $xpath->query('//main//a[@href="'.$url.'"]')->length);
        }
        $response->assertSee('+55 (11) 3333-2222')->assertSee('+55 (11) 99999-2222')->assertSee('Falar pelo WhatsApp');
        $this->assertSame(1, $xpath->query('//main//address')->length);
        $this->assertSame(0, $xpath->query('//main//form')->length);
        $this->assertGreaterThanOrEqual(1, $xpath->query('//header//a[@href="https://wa.me/5511999992222"]')->length);
    }

    public function test_missing_settings_do_not_invent_channels_or_whatsapp(): void
    {
        $response = $this->get('/contato')->assertOk()->assertSee('Os canais de contato serão disponibilizados em breve.')
            ->assertDontSee('https://wa.me/')->assertDontSee('Falar pelo WhatsApp');
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(0, $xpath->query('//main//address | //main//form | //main//a[starts-with(@href,"tel:") or starts-with(@href,"mailto:")]')->length);
        $this->assertDatabaseCount('store_settings', 0);
        $this->post('/contato', ['email' => 'visitor@example.com', 'message' => 'Teste'])->assertStatus(405);
    }

    public function test_partial_contact_data_omits_unconfigured_cards_and_formats_partial_address(): void
    {
        app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'SABA', 'email' => 'loja@example.com', 'city' => 'Campinas']);
        $response = $this->get('/contato')->assertOk()->assertViewHas('addressLines', ['Campinas'])
            ->assertDontSee('https://wa.me/')->assertDontSee('Falar pelo WhatsApp');
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(2, $xpath->query('//main//section[h2]')->length);
        $this->assertSame(0, $xpath->query('//main//a[starts-with(@href,"tel:")]')->length);
        $this->assertDatabaseCount('store_settings', 1);
    }

    public function test_unusable_whatsapp_number_does_not_create_a_broken_link(): void
    {
        StoreSetting::query()->create(['store_name' => 'SABA', 'whatsapp' => '123']);
        $this->get('/contato')->assertOk()->assertDontSee('https://wa.me/')->assertDontSee('Falar pelo WhatsApp');
    }

    public function test_about_copy_can_be_edited_without_changing_the_view(): void
    {
        config(['storefront.about' => [
            'title' => 'Quem Somos', 'introduction' => 'Texto institucional editável.',
            'sections' => [['title' => 'Uma seção', 'text' => 'Conteúdo atualizado.']],
        ]]);
        $this->get('/quem-somos')->assertOk()->assertSee('Texto institucional editável.')->assertSee('Conteúdo atualizado.');
    }
}
