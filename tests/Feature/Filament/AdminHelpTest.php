<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\Help;
use App\Filament\Support\BundledHelpArticles;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminHelpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
    }

    public function test_help_is_not_accessible_to_guests(): void
    {
        $this->get(Help::getUrl())->assertRedirect();
    }

    public function test_authenticated_admin_can_read_manual_and_open_shipping_topic(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(Help::getUrl())
            ->assertOk()
            ->assertSee('CENTRAL DE AJUDA')
            ->assertSee('Primeiros passos e mapa do painel')
            ->assertSee('Gerar, imprimir e postar etiquetas');

        $this->get(Help::getUrl(['topico' => 'etiquetas']))
            ->assertOk()
            ->assertSee('Nota Fiscal')
            ->assertSee('Declaração de conteúdo')
            ->assertSee('Gerar etiqueta');
    }

    public function test_search_matches_article_body_and_handles_no_results(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(Help::getUrl(['q' => 'CHECKOUT_CANCELED']))
            ->assertOk()
            ->assertSee('Criar conta e configurar o Asaas');

        $this->get(Help::getUrl(['q' => 'termoquenaoexiste637481']))
            ->assertOk()
            ->assertSee('0 resultados encontrados');
    }

    public function test_invalid_topic_does_not_read_arbitrary_files(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(Help::getUrl(['topico' => '../../.env']))
            ->assertOk()
            ->assertSee('Primeiros passos e mapa do painel');
    }
    public function test_the_manual_has_an_index_and_previous_next_navigation(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(Help::getUrl(['topico' => 'casos-praticos']))
            ->assertOk()
            ->assertSee('Neste tutorial')
            ->assertSee('Cenário 1: cadastrar o primeiro produto e vender')
            ->assertSee('href="#cenario-1-cadastrar-o-primeiro-produto-e-vender"', false)
            ->assertSee('rel="prev"', false)
            ->assertSee('rel="next"', false)
            ->assertSee('Imprimir / Salvar PDF');
    }

    public function test_the_help_search_finds_new_articles(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(Help::getUrl(['q' => 'logística reversa']))
            ->assertOk()
            ->assertSee('Pós-venda, devoluções e estornos');
    }

    public function test_all_configured_articles_are_written_and_bundled(): void
    {
        $topics = (array) config('admin_help.topics', []);
        $this->assertCount(27, $topics);
        foreach (array_keys($topics) as $slug) {
            $this->assertFileExists(resource_path('help/'.$slug.'.md'), $slug);
            $article = BundledHelpArticles::get($slug);
            $this->assertNotNull($article, $slug);
            $this->assertGreaterThan(900, mb_strlen($article), $slug);
            $this->assertStringContainsString('## ', $article, $slug);
            $this->assertStringNotContainsString('Artigo temporariamente indisponível', $article, $slug);
        }
    }

    public function test_every_article_renders_real_content_in_admin(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (array_keys((array) config('admin_help.topics', [])) as $slug) {
            $response = $this->get(Help::getUrl(['topico' => $slug]));
            $response->assertOk()->assertDontSee('Artigo temporariamente indisponível');
            $response->assertSee('saba-help-content', false);
            $response->assertSee('saba-help-toc', false);
        }
    }

}
