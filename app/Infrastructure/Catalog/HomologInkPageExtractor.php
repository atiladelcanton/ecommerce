<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

/** Extrai somente descrição técnica e galeria da página individual do produto. */
final class HomologInkPageExtractor
{
    /** @return array{description:string,images:list<string>} */
    public function extract(string $html, string $pageUrl, string $productName): array
    {
        if (! class_exists(DOMDocument::class)) {
            throw new RuntimeException('Extensão PHP DOM (php-xml) necessária para extrair a descrição.');
        }
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            if (! $dom->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                throw new RuntimeException('HTML inválido');
            }
            $xpath = new DOMXPath($dom);
            $description = '';
            foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " short-description ")]') ?: [] as $node) {
                $description = $this->paragraphs($node);
                if ($description !== '') {
                    break;
                }
            }
            // Falta da descrição técnica não deve ser disfarçada com meta SEO.
            // O comando pode usar o resumo editorial enviado no próprio pacote.
            $images = [];
            $nameKey = $this->normalize($productName);
            foreach ($xpath->query('//h1[1]/preceding::img') ?: [] as $img) {
                if (! $img instanceof DOMElement || ! str_contains($this->normalize($img->getAttribute('alt')), $nameKey)) {
                    continue;
                }
                foreach (['data-zoom-image', 'data-large-image', 'data-original', 'data-src', 'src'] as $attribute) {
                    $value = trim($img->getAttribute($attribute));
                    if ($value === '' || str_starts_with($value, 'data:')) {
                        continue;
                    }
                    $candidate = $this->absolute($value, $pageUrl);
                    if ($candidate !== null && str_contains((string) parse_url($candidate, PHP_URL_HOST), 'cdn.vnda.com.br')) {
                        $pathKey = preg_replace('~/(?:\d+x)/~', '/', (string) parse_url($candidate, PHP_URL_PATH)) ?? $candidate;
                        $images[$pathKey] = $candidate;
                        break;
                    }
                }
                if (count($images) >= 12) {
                    break;
                }
            }
            return ['description' => $description, 'images' => array_values($images)];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));
    }

    private function absolute(string $src, string $pageUrl): ?string
    {
        if (str_starts_with($src, '//')) {
            $src = 'https:'.$src;
        } elseif (str_starts_with($src, '/')) {
            $origin = parse_url($pageUrl);
            $src = ($origin['scheme'] ?? 'https').'://'.($origin['host'] ?? '').$src;
        }
        return filter_var($src, FILTER_VALIDATE_URL) && strtolower((string) parse_url($src, PHP_URL_SCHEME)) === 'https' ? $src : null;
    }

    private function paragraphs(DOMNode $root): string
    {
        $parts = [];
        $walk = static function (DOMNode $node) use (&$walk, &$parts): void {
            if ($node->nodeType === XML_TEXT_NODE) {
                $parts[] = preg_replace('/\s+/u', ' ', $node->nodeValue ?? '') ?? '';
                return;
            }
            if (! $node instanceof DOMElement) {
                return;
            }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'noscript', 'template', 'iframe', 'svg', 'form', 'button'], true) || $node->hasAttribute('hidden')) {
                return;
            }
            if ($tag === 'br') {
                $parts[] = "\n";
                return;
            }
            $block = in_array($tag, ['p', 'div', 'li', 'ul', 'ol', 'section', 'h2', 'h3'], true);
            if ($block) {
                $parts[] = "\n";
            }
            if ($tag === 'li') {
                $parts[] = '• ';
            }
            foreach ($node->childNodes as $child) {
                $walk($child);
            }
            if ($block) {
                $parts[] = "\n";
            }
        };
        $walk($root);
        $text = str_replace("\xc2\xa0", ' ', implode('', $parts));
        $text = preg_replace('/[ \t]*\n[ \t]*/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;
        return trim($text);
    }
}
