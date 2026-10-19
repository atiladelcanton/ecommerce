<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Actions\AI\GenerateProductContentAction;
use App\Actions\AI\UpdateAiSettingsAction;
use App\Domain\AI\Models\AiSetting;
use App\Domain\AI\Models\AiUsageLog;
use App\Domain\Catalog\Models\Category;
use App\Infrastructure\AI\AiProviderRegistry;
use App\Livewire\Admin\ProductAiAssistant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductAiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $this->actingAs(User::factory()->create());
    }

    public function test_registry_exposes_groq_as_implemented_provider(): void
    {
        $registry = app(AiProviderRegistry::class);

        $this->assertSame(['groq' => 'Groq'], $registry->implementedOptions());
        $this->assertSame('groq', $registry->provider('groq')->key());
    }

    public function test_api_key_is_encrypted_at_rest_and_blank_update_preserves_it(): void
    {
        app(UpdateAiSettingsAction::class)->execute([
            'provider' => 'groq',
            'is_enabled' => true,
            'model' => 'openai/gpt-oss-120b',
            'api_key' => 'gsk_test_secret',
            'store_instructions' => null,
            'monthly_request_limit' => 100,
        ]);

        $raw = DB::table('ai_settings')->where('id', AiSetting::MAIN_ID)->value('api_key');
        $this->assertNotSame('gsk_test_secret', $raw);
        $this->assertSame('gsk_test_secret', AiSetting::current()->api_key);

        app(UpdateAiSettingsAction::class)->execute([
            'provider' => 'groq',
            'is_enabled' => true,
            'model' => 'openai/gpt-oss-20b',
            'api_key' => '',
            'store_instructions' => 'Tom objetivo.',
            'monthly_request_limit' => null,
        ]);

        $this->assertSame('gsk_test_secret', AiSetting::current()->api_key);
        $this->assertSame('openai/gpt-oss-20b', AiSetting::current()->model);
    }

    public function test_product_content_generation_uses_structured_output_and_logs_usage(): void
    {
        $category = Category::factory()->create(['name' => 'Tecidos', 'slug' => 'tecidos', 'is_active' => true]);
        $this->configure();

        Http::fake([
            'https://api.groq.com/openai/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode([
                            'name' => 'Nylon Amarelo 77 Fios',
                            'short_description' => 'Nylon amarelo de 77 fios vendido por metro.',
                            'description' => '<p>Nylon amarelo de <strong>77 fios</strong>, vendido por metro.</p>',
                            'seo_title' => 'Nylon Amarelo 77 Fios',
                            'seo_description' => 'Nylon amarelo 77 fios vendido por metro.',
                            'category_slug' => 'tecidos',
                        ], JSON_UNESCAPED_UNICODE),
                    ]],
                ]],
                'usage' => ['input_tokens' => 123, 'output_tokens' => 77, 'total_tokens' => 200],
            ], 200),
        ]);

        $result = app(GenerateProductContentAction::class)->execute(
            mode: 'generate',
            brief: 'Nylon amarelo, 77 fios, vendido por metro.',
            current: ['name' => 'Nylon'],
        );

        $this->assertSame('Nylon Amarelo 77 Fios', $result['name']);
        $this->assertSame($category->id, $result['category_id']);
        $this->assertStringContainsString('<strong>77 fios</strong>', $result['description']);
        $this->assertDatabaseHas('ai_usage_logs', [
            'provider' => 'groq',
            'model' => 'openai/gpt-oss-120b',
            'successful' => true,
            'input_tokens' => 123,
            'output_tokens' => 77,
            'total_tokens' => 200,
        ]);

        Http::assertSent(function ($request): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.groq.com/openai/v1/responses'
                && data_get($payload, 'text.format.type') === 'json_schema'
                && data_get($payload, 'text.format.strict') === true
                && data_get($payload, 'model') === 'openai/gpt-oss-120b';
        });
    }

    public function test_product_content_generation_ignores_gpt_oss_reasoning_text_and_reads_final_output_text(): void
    {
        $category = Category::factory()->create(['name' => 'Tecidos', 'slug' => 'tecidos', 'is_active' => true]);
        $this->configure();

        Http::fake([
            'https://api.groq.com/openai/v1/responses' => Http::response([
                'status' => 'completed',
                'output' => [
                    [
                        'type' => 'reasoning',
                        'content' => [[
                            'type' => 'reasoning_text',
                            'text' => 'Preciso produzir um cadastro de produto em JSON.',
                        ]],
                    ],
                    [
                        'type' => 'message',
                        'role' => 'assistant',
                        'content' => [[
                            'type' => 'output_text',
                            'text' => json_encode([
                                'name' => 'Nylon Amarelo 77 Fios',
                                'short_description' => 'Nylon amarelo de 77 fios vendido por metro.',
                                'description' => '<p>Nylon amarelo de <strong>77 fios</strong>.</p>',
                                'seo_title' => 'Nylon Amarelo 77 Fios',
                                'seo_description' => 'Nylon amarelo 77 fios vendido por metro.',
                                'category_slug' => 'tecidos',
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ],
                'usage' => ['input_tokens' => 50, 'output_tokens' => 30, 'total_tokens' => 80],
            ], 200),
        ]);

        $result = app(GenerateProductContentAction::class)->execute(
            mode: 'generate',
            brief: 'Nylon amarelo, 77 fios, vendido por metro.',
            current: ['name' => 'Nylon'],
        );

        $this->assertSame('Nylon Amarelo 77 Fios', $result['name']);
        $this->assertSame($category->id, $result['category_id']);
    }

    public function test_component_only_applies_selected_text_fields_and_never_commercial_fields(): void
    {
        $this->configure();
        Http::fake([
            'https://api.groq.com/openai/v1/responses' => Http::response([
                'output' => [[
                    'content' => [[
                        'text' => json_encode([
                            'name' => 'Camiseta Preta Oversized',
                            'short_description' => 'Camiseta preta oversized.',
                            'description' => '<p>Camiseta preta oversized.</p>',
                            'seo_title' => 'Camiseta Preta Oversized',
                            'seo_description' => 'Camiseta preta oversized.',
                            'category_slug' => null,
                        ]),
                    ]],
                ]],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 20, 'total_tokens' => 30],
            ]),
        ]);

        Livewire::test(ProductAiAssistant::class, ['initial' => [
            'name' => 'Camiseta',
            'price' => 9990,
            'stock' => 5,
        ]])
            ->set('brief', 'Camiseta preta oversized.')
            ->call('generate')
            ->set('applyFields.name', true)
            ->set('applyFields.short_description', false)
            ->call('apply')
            ->assertDispatched('product-ai-suggestions-applied');
    }


    public function test_assistant_accepts_live_filament_rich_editor_json_state(): void
    {
        $initial = [
            'name' => 'Nylon',
            'description' => [
                'type' => 'doc',
                'content' => [[
                    'type' => 'paragraph',
                    'content' => [[
                        'type' => 'text',
                        'text' => 'Nylon amarelo 77 fios',
                        'marks' => [['type' => 'bold']],
                    ]],
                ]],
            ],
        ];

        Livewire::test(ProductAiAssistant::class, ['initial' => $initial])
            ->assertOk()
            ->assertSee('Nylon amarelo 77 fios');
    }

    public function test_monthly_limit_prevents_external_request(): void
    {
        $this->configure(limit: 1);
        AiUsageLog::query()->create([
            'provider' => 'groq', 'model' => 'openai/gpt-oss-120b', 'action' => 'product_content',
            'input_tokens' => 1, 'output_tokens' => 1, 'total_tokens' => 2, 'successful' => true,
        ]);
        Http::fake();

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            app(GenerateProductContentAction::class)->execute('generate', 'Produto de teste', []);
        } finally {
            Http::assertNothingSent();
        }
    }

    private function configure(?int $limit = null): void
    {
        app(UpdateAiSettingsAction::class)->execute([
            'provider' => 'groq',
            'is_enabled' => true,
            'model' => 'openai/gpt-oss-120b',
            'api_key' => 'gsk_test_secret',
            'store_instructions' => 'Não use emojis.',
            'monthly_request_limit' => $limit,
        ]);
    }
}
