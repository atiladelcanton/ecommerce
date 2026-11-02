<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Support\BundledHelpArticles;
use PHPUnit\Framework\TestCase;

final class BundledHelpArticlesTest extends TestCase
{
    public function test_every_help_article_matches_the_text_visible_in_the_panel(): void
    {
        $root = dirname(__DIR__, 3);
        $topics = (require $root.'/config/admin_help.php')['topics'];

        foreach (array_keys($topics) as $slug) {
            $path = $root.'/resources/help/'.$slug.'.md';
            self::assertFileExists($path, $slug);
            $markdown = file_get_contents($path);
            self::assertIsString($markdown);
            self::assertSame(rtrim($markdown, "\r\n"), BundledHelpArticles::get($slug), $slug);
        }
    }

    public function test_product_guide_explains_generic_variations_and_saving(): void
    {
        $article = BundledHelpArticles::get('produtos');
        self::assertNotNull($article);
        self::assertStringContainsString('Adicionar característica', $article);
        self::assertStringContainsString('Gerar / atualizar combinações', $article);
        self::assertStringContainsString('Aplicar preço / estoque a todos', $article);
        self::assertStringContainsString('compartilhado', $article);
        self::assertStringContainsString('Voltagem', $article);
        self::assertStringContainsString('Fragrância', $article);
        self::assertStringContainsString('Assistente de IA', $article);
    }
}
