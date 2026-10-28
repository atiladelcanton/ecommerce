<?php

declare(strict_types=1);

namespace Tests\Feature\Blog;

use App\Actions\Blog\GetPublicBlogAction;
use App\Actions\Blog\SaveBlogPostAction;
use App\Actions\Blog\RegisterBlogTermAction;
use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Blog\Models\BlogPost;
use App\Domain\Blog\Models\BlogTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class BlogModuleTest extends TestCase
{
    use RefreshDatabase;

    private function enable(bool $showOnHome = false): void
    {
        app(UpdateStoreSettingsAction::class)->execute([
            'store_name' => 'SABA',
            'blog_enabled' => true,
            'blog_show_on_home' => $showOnHome,
        ]);
    }

    private function post(array $data = []): BlogPost
    {
        return app(SaveBlogPostAction::class)->execute(array_merge([
            'title' => 'Introdução à serigrafia',
            'content' => '<h2>Conheça a técnica</h2><p>Um guia prático.</p>',
            'status' => 'published',
        ], $data));
    }

    public function test_categories_and_tags_are_registered_and_reusable_without_changing_blog_posts_schema(): void
    {
        $post = $this->post([
            'category' => '  Dicas   de Serigrafia ',
            'tags' => ['Telas', '  Tintas  ', 'telas', 'TINTAS'],
        ]);

        $this->assertSame('Dicas de Serigrafia', $post->category);
        $this->assertSame(['Telas', 'Tintas'], $post->tags);
        $this->assertDatabaseHas('blog_terms', ['type' => 'category', 'name' => 'Dicas de Serigrafia', 'slug' => 'dicas-de-serigrafia']);
        $this->assertDatabaseHas('blog_terms', ['type' => 'tag', 'name' => 'Telas', 'slug' => 'telas']);
        $this->assertDatabaseCount('blog_terms', 3);

        $other = $this->post([
            'title' => 'Novo artigo sobre tintas',
            'category' => 'dicas de serigrafia',
            'tags' => ['TELAS', 'Novidades'],
        ]);

        $this->assertSame('Dicas de Serigrafia', $other->category);
        $this->assertSame(['Telas', 'Novidades'], $other->tags);
        $this->assertDatabaseCount('blog_terms', 4);
        $this->assertSame(['Dicas de Serigrafia'], BlogTerm::query()->categories()->pluck('name')->all());

        $post->delete();
        $other->delete();
        $this->assertDatabaseCount('blog_terms', 4); // Dicionário independente dos artigos.
    }

    public function test_categories_can_be_pre_registered_and_tags_are_not_saved_if_post_validation_fails(): void
    {
        $term = app(RegisterBlogTermAction::class)->execute('category', '  Inspiração ');
        $this->assertSame('Inspiração', $term->name);
        $this->assertSame($term->id, app(RegisterBlogTermAction::class)->execute('category', 'inspiracao')->id);

        $this->post();
        try {
            $this->post(['title' => 'Introdução à serigrafia', 'tags' => ['Sem cadastro']]);
            $this->fail('A segunda postagem com slug igual deveria falhar.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('slug', $e->errors());
        }
        $this->assertDatabaseMissing('blog_terms', ['type' => 'tag', 'slug' => 'sem-cadastro']);
    }

    public function test_blog_is_disabled_by_default_and_hidden_from_navigation_and_home(): void
    {
        $post = $this->post();
        $this->get('/blog')->assertNotFound();
        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('Últimas do Blog')->assertDontSee('href="'.route('store.blog.index').'"', false);
    }

    public function test_blog_enabled_displays_link_and_published_post_but_not_drafts(): void
    {
        $this->enable();
        $post = $this->post(['category' => 'Dicas', 'tags' => ['Telas', 'Tintas']]);
        $draft = $this->post(['title' => 'Segredo ainda não revelado', 'status' => 'draft']);
        $this->get('/')->assertOk()->assertSee('Blog')->assertDontSee('Últimas do Blog');
        $this->get('/blog')->assertOk()->assertSee('Introdução à serigrafia')->assertDontSee($draft->title);
        $this->get('/blog/'.$post->slug)->assertOk()->assertSee('Conheça a técnica')->assertSee('Tintas');
        $this->get('/blog/'.$draft->slug)->assertNotFound();
    }

    public function test_home_section_requires_both_flags_and_published_posts(): void
    {
        $this->enable(true);
        $this->get('/')->assertOk()->assertDontSee('Últimas do Blog');
        $post = $this->post();
        $this->get('/')->assertOk()->assertSee('Últimas do Blog')->assertSee($post->title);
        $this->enable(false);
        $this->get('/')->assertOk()->assertDontSee('Últimas do Blog');
        $this->enable(true);
        app(UpdateStoreSettingsAction::class)->execute(['store_name' => 'SABA', 'blog_enabled' => false]);
        $this->get('/')->assertOk()->assertDontSee('Últimas do Blog');
        $this->get('/blog')->assertNotFound();
    }

    public function test_scheduled_posts_are_hidden_until_their_publication_time(): void
    {
        $this->enable(true);
        $post = $this->post(['published_at' => now()->addDay()->format('Y-m-d H:i:s')]);
        $this->assertCount(0, app(GetPublicBlogAction::class)->latest());
        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->travel(2)->days();
        $this->get('/blog/'.$post->slug)->assertOk();
        $this->get('/')->assertSee('Últimas do Blog');
    }

    public function test_editor_sanitizes_content_and_keeps_slug_stable_on_edit(): void
    {
        $post = $this->post([
            'content' => '<p>Conteúdo seguro</p><script>alert(1)</script><a href="javascript:alert(1)">Clique</a>',
        ]);
        $this->assertStringNotContainsString('<script', $post->content);
        $this->assertStringNotContainsString('javascript:', $post->content);
        $updated = app(SaveBlogPostAction::class)->execute([
            'title' => 'Novo título', 'status' => 'draft', 'content' => '<p>Texto novo</p>',
        ], $post);
        $this->assertSame('introducao-a-serigrafia', $updated->slug);
        $this->assertNull($updated->published_at);
    }

    public function test_rejects_duplicate_slug_and_empty_published_content(): void
    {
        $this->post();
        try {
            $this->post(['title' => 'Introdução à serigrafia']);
            $this->fail('Duplicate slug must be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('slug', $e->errors());
        }
        try {
            $this->post(['title' => 'Artigo vazio', 'content' => '']);
            $this->fail('Published content must not be empty.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('content', $e->errors());
        }
    }
}
