<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Actions\AI\GenerateBlogContentAction;
use App\Actions\AI\UpdateAiSettingsAction;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiUsageLog;
use App\Domain\Blog\Models\BlogPost;
use App\Livewire\Admin\BlogAiAssistant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class BlogAiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $this->actingAs(User::factory()->create());
    }

    private function configure(?int $limit = null): void
    {
        app(UpdateAiSettingsAction::class)->execute([
            'provider' => 'groq',
            'is_enabled' => true,
            'model' => 'openai/gpt-oss-120b',
            'api_key' => 'gsk_test_secret',
            'store_instructions' => 'Tom claro e profissional.',
            'monthly_request_limit' => $limit,
        ]);
    }

    private function fakeResponse(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/responses' => Http::response([
                'output' => [
                    ['type' => 'reasoning', 'content' => [['type' => 'reasoning_text', 'text' => 'Rascunho interno não deve aparecer.']]],
                    ['type' => 'message', 'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode([
                            'title' => 'Cuidados com a tela de serigrafia',
                            'excerpt' => 'Cuidados de conservação no cotidiano.',
                            'content' => '<h2>Cuidados básicos</h2><p>Limpe a tela corretamente.</p><script>alert(1)</script>',
                            'seo_title' => 'Como cuidar da tela de serigrafia',
                            'seo_description' => 'Orientações de conservação de telas.',
                        ], JSON_UNESCAPED_UNICODE),
                    ]]],
                ],
                'usage' => ['input_tokens' => 70, 'output_tokens' => 120, 'total_tokens' => 190],
            ], 200),
        ]);
    }

    public function test_writes_blog_post_using_existing_groq_configuration_without_persisting_it(): void
    {
        $this->configure();
        $this->fakeResponse();

        $fields = app(GenerateBlogContentAction::class)->execute('generate', 'Artigo sobre telas de serigrafia', [], 'didático', 'médio');
        $this->assertSame('Cuidados com a tela de serigrafia', $fields['title']);
        $this->assertStringContainsString('<h2>Cuidados básicos</h2>', $fields['content']);
        $this->assertStringNotContainsString('<script', $fields['content']);
        $this->assertDatabaseCount('blog_posts', 0);
        $this->assertDatabaseHas('ai_usage_logs', [
            'action' => 'blog_content', 'product_id' => null, 'successful' => true,
            'input_tokens' => 70, 'output_tokens' => 120, 'total_tokens' => 190,
        ]);
        Http::assertSent(fn ($request): bool =>
            $request->url() === 'https://api.groq.com/openai/v1/responses'
            && data_get($request->data(), 'text.format.strict') === true
            && data_get($request->data(), 'text.format.name') === 'blog_content_suggestion'
            && data_get($request->data(), 'model') === 'openai/gpt-oss-120b');
    }

    public function test_improvement_includes_existing_rich_editor_content_and_preserves_post_publication(): void
    {
        $this->configure();
        $this->fakeResponse();
        $post = BlogPost::query()->create([
            'title' => 'Texto original', 'slug' => 'texto-original', 'content' => '<p>Meu texto original</p>',
            'status' => 'draft', 'published_at' => null,
        ]);
        app(GenerateBlogContentAction::class)->execute('improve', 'Torne mais didático.', [
            'title' => $post->title,
            'content' => ['type' => 'doc', 'content' => [[
                'type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Texto editor atual']],
            ]]],
        ], 'informativo', 'longo');
        $this->assertSame('Meu texto original', $post->refresh()->content);
        $this->assertSame('draft', $post->status);
        Http::assertSent(function ($request): bool {
            return str_contains((string) data_get($request->data(), 'input'), 'Texto editor atual')
                && str_contains((string) data_get($request->data(), 'instructions'), 'preservando o sentido');
        });
    }

    public function test_component_selectively_applies_suggestions_without_modifying_metadata(): void
    {
        $this->configure();
        $this->fakeResponse();
        Livewire::test(BlogAiAssistant::class, ['initial' => [
            'title' => 'Meu título', 'content' => '<p>Meu conteúdo.</p>',
            'status' => 'draft', 'slug' => 'meu-titulo', 'cover_image' => 'blog/covers/abc123.jpg',
        ]])
            ->set('brief', 'Melhore o texto mantendo o sentido.')
            ->call('generate')
            ->set('applyFields.title', false)
            ->set('applyFields.seo_title', false)
            ->call('apply')
            ->assertDispatched('blog-ai-suggestions-applied', function (string $event, array $params): bool {
                return ! array_key_exists('title', $params['fields'])
                    && ! array_key_exists('status', $params['fields'])
                    && ! array_key_exists('slug', $params['fields'])
                    && isset($params['fields']['content']);
            });
    }

    public function test_monthly_request_limit_blocks_calls_to_provider(): void
    {
        $this->configure(limit: 1);
        AiUsageLog::query()->create([
            'provider' => 'groq', 'model' => 'openai/gpt-oss-120b', 'action' => 'product_content',
            'input_tokens' => 1, 'output_tokens' => 1, 'total_tokens' => 2, 'successful' => true,
        ]);
        Http::fake();
        $this->expectException(ValidationException::class);
        try {
            app(GenerateBlogContentAction::class)->execute('generate', 'Tema de teste', []);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_requires_authentication_and_does_not_contact_provider(): void
    {
        $this->configure();
        auth('web')->logout();
        Http::fake();
        try {
            app(GenerateBlogContentAction::class)->execute('generate', 'Tema válido', []);
            $this->fail('A IA não pode ser acessada por visitante.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_rejects_improvement_without_existing_body(): void
    {
        $this->configure();
        Http::fake();
        $this->expectException(ValidationException::class);
        try {
            app(GenerateBlogContentAction::class)->execute('improve', 'Melhore', ['title' => 'Só título']);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_invalid_response_logs_failure_without_publishing(): void
    {
        $this->configure();
        Http::fake(['https://api.groq.com/openai/v1/responses' => Http::response([
            'output_text' => '{json inválido',
        ], 200)]);
        $this->expectException(AiProviderFailed::class);
        try {
            app(GenerateBlogContentAction::class)->execute('generate', 'Tema de teste', []);
        } finally {
            $this->assertDatabaseHas('ai_usage_logs', ['action' => 'blog_content', 'successful' => false, 'error_code' => 'invalid_json']);
            $this->assertDatabaseCount('blog_posts', 0);
        }
    }
}
