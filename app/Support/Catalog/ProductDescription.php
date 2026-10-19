<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class ProductDescription
{
    /**
     * Filament 5 keeps RichEditor state as TipTap/ProseMirror JSON while the
     * form is open, even though the database representation is HTML.
     *
     * @param mixed $description HTML/plain text from persistence or RichEditor JSON state.
     */
    public static function html(mixed $description): string
    {
        if ($description === null || $description === '' || $description === []) {
            return '';
        }

        if (is_array($description)) {
            $description = self::richEditorJsonToHtml($description);
        }

        if (! is_string($description) || $description === '') {
            return '';
        }

        // Preserve legacy plain-text descriptions, including their line breaks.
        if (! preg_match('/<\/?[a-z][^>]*>/i', $description)) {
            return nl2br(e($description), false);
        }

        return self::sanitizer()->sanitize($description);
    }

    /**
     * Plain representation useful when sending the current editor contents to AI.
     */
    public static function text(mixed $description): ?string
    {
        $html = self::html($description);
        if ($html === '') {
            return null;
        }

        $html = preg_replace('/<br\s*\/?\s*>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<\/(p|h[1-6]|li|blockquote|ul|ol)>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[\t ]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    /** @param array<string|int, mixed> $state */
    private static function richEditorJsonToHtml(array $state): string
    {
        // Be tolerant if a future Filament version wraps an already-rendered value.
        foreach (['html', 'value'] as $key) {
            if (isset($state[$key]) && is_string($state[$key])) {
                return $state[$key];
            }
        }

        return self::renderNode($state);
    }

    /** @param array<string|int, mixed> $node */
    private static function renderNode(array $node): string
    {
        // Some states can arrive as a numeric list of nodes.
        if (array_is_list($node)) {
            return implode('', array_map(
                static fn (mixed $child): string => is_array($child) ? self::renderNode($child) : '',
                $node,
            ));
        }

        $type = is_string($node['type'] ?? null) ? $node['type'] : null;
        $children = is_array($node['content'] ?? null)
            ? implode('', array_map(
                static fn (mixed $child): string => is_array($child) ? self::renderNode($child) : '',
                $node['content'],
            ))
            : '';

        if ($type === 'text') {
            $text = e(is_string($node['text'] ?? null) ? $node['text'] : '');
            $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

            foreach ($marks as $mark) {
                if (! is_array($mark) || ! is_string($mark['type'] ?? null)) {
                    continue;
                }

                $text = match ($mark['type']) {
                    'bold' => '<strong>'.$text.'</strong>',
                    'italic' => '<em>'.$text.'</em>',
                    'underline' => '<u>'.$text.'</u>',
                    'strike' => '<s>'.$text.'</s>',
                    'code' => '<code>'.$text.'</code>',
                    'link' => self::renderLinkMark($text, $mark),
                    default => $text,
                };
            }

            return $text;
        }

        return match ($type) {
            'doc' => $children,
            'paragraph' => '<p>'.$children.'</p>',
            'heading' => self::renderHeading($children, $node),
            'bulletList' => '<ul>'.$children.'</ul>',
            'orderedList' => '<ol>'.$children.'</ol>',
            'listItem' => '<li>'.$children.'</li>',
            'blockquote' => '<blockquote>'.$children.'</blockquote>',
            'hardBreak' => '<br>',
            'horizontalRule' => '<hr>',
            // Unknown structural nodes are deliberately flattened to their safe
            // children rather than serialized as JSON or discarded wholesale.
            default => $children,
        };
    }

    /** @param array<string|int, mixed> $node */
    private static function renderHeading(string $children, array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $level = (int) ($attrs['level'] ?? 2);
        $level = max(2, min(4, $level));

        return sprintf('<h%d>%s</h%d>', $level, $children, $level);
    }

    /** @param array<string|int, mixed> $mark */
    private static function renderLinkMark(string $text, array $mark): string
    {
        $attrs = is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [];
        $href = is_string($attrs['href'] ?? null) ? trim($attrs['href']) : '';
        if ($href === '') {
            return $text;
        }

        $title = is_string($attrs['title'] ?? null) && $attrs['title'] !== ''
            ? ' title="'.e($attrs['title']).'"'
            : '';

        return '<a href="'.e($href).'"'.$title.'>'.$text.'</a>';
    }

    private static function sanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('a', ['href', 'title'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks();

        foreach (['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'code', 'ul', 'ol', 'li', 'blockquote', 'hr'] as $tag) {
            $config = $config->allowElement($tag);
        }

        foreach (['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'img'] as $tag) {
            $config = $config->dropElement($tag);
        }

        return new HtmlSanitizer($config);
    }
}
