<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog;

use App\Support\Catalog\ProductDescription;
use Tests\TestCase;

final class ProductDescriptionTest extends TestCase
{
    public function test_editor_formatting_and_emojis_are_preserved(): void
    {
        $html = '<h2>Equipamento</h2><p><strong>Robusto</strong> <em>compacto</em> <u>versátil</u> ✅</p>'
            .'<ul><li>Primeiro</li></ul><ol><li>Segundo</li></ol><blockquote>Destaque</blockquote>'
            .'<p><a href="https://example.com/manual">Manual</a></p>';
        $this->assertSame($html, ProductDescription::html($html));
    }

    public function test_active_html_and_uncontrolled_visual_elements_are_removed(): void
    {
        $html = '<script>alert(1)</script><style>body{display:none}</style>'
            .'<p style="position:fixed" onclick="alert(2)">Texto seguro</p>'
            .'<a href="javascript:alert(3)">Link</a><a href="data:text/html,bad">Outro</a>'
            .'<iframe src="https://example.com"></iframe><svg onload="alert(4)"></svg>'
            .'<img src="bad" onerror="alert(5)">';
        $safe = ProductDescription::html($html);
        $this->assertStringContainsString('<p>Texto seguro</p>', $safe);
        foreach (['script', 'style', 'onclick', 'javascript:', 'data:', 'iframe', 'svg', '<img', 'alert('] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $safe);
        }
    }

    public function test_legacy_plain_text_remains_escaped_with_line_breaks(): void
    {
        $this->assertSame("Máquina &amp; acessórios<br>\nSegunda linha", ProductDescription::html("Máquina & acessórios\nSegunda linha"));
        $this->assertSame('', ProductDescription::html(null));
    }

    public function test_filament_rich_editor_json_state_is_rendered_safely(): void
    {
        $state = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 2],
                    'content' => [['type' => 'text', 'text' => 'Nylon 77 fios']],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Nylon ', 'marks' => [['type' => 'bold']]],
                        ['type' => 'text', 'text' => 'amarelo'],
                    ],
                ],
            ],
        ];

        $this->assertSame('<h2>Nylon 77 fios</h2><p><strong>Nylon </strong>amarelo</p>', ProductDescription::html($state));
        $this->assertSame("Nylon 77 fios\nNylon amarelo", ProductDescription::text($state));
    }
}
