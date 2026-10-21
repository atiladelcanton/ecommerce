<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use Illuminate\Support\Str;

/** Extração conservadora de HTML público; campos ausentes permanecem vazios. */
final class CatalogHtmlExtractor
{
    /** @return list<string> */
    public function links(string $html, string $base): array
    {
        preg_match_all('~<a\b[^>]*\bhref\s*=\s*(?:["\']([^"\']+)["\']|([^\s>]+))~i', $html, $matches, PREG_SET_ORDER);
        $urls = [];
        foreach ($matches as $match) {
            $href = html_entity_decode($match[1] ?: ($match[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $url = $this->absoluteUrl($href, $base);
            if ($url !== null) {
                $urls[] = $url;
            }
        }
        return array_values(array_unique($urls));
    }

    public function absoluteUrl(string $candidate, string $base): ?string
    {
        $candidate = trim($candidate);
        if ($candidate === '' || str_starts_with($candidate, '#') || preg_match('~^(?:javascript|data|mailto|tel):~i', $candidate)) {
            return null;
        }
        $parsedBase = parse_url($base);
        if (! is_array($parsedBase) || ! isset($parsedBase['scheme'], $parsedBase['host'])) {
            return null;
        }
        $scheme = strtolower($parsedBase['scheme']);
        $origin = $scheme.'://'.$parsedBase['host'].(isset($parsedBase['port']) ? ':'.$parsedBase['port'] : '');
        if (str_starts_with($candidate, '//')) {
            $candidate = $scheme.':'.$candidate;
        } elseif (! preg_match('~^https?://~i', $candidate)) {
            if (str_starts_with($candidate, '?')) {
                $candidate = ($parsedBase['path'] ?? '/').$candidate;
            } elseif (! str_starts_with($candidate, '/')) {
                $candidate = dirname($parsedBase['path'] ?? '/').'/'.$candidate;
            }
            $candidate = $origin.$candidate;
        }
        $url = parse_url($candidate);
        if (! is_array($url) || ! isset($url['scheme'], $url['host']) || ! in_array(strtolower($url['scheme']), ['http', 'https'], true)) {
            return null;
        }
        $pathSegments = explode('/', $url['path'] ?? '/');
        $clean = [];
        foreach ($pathSegments as $segment) {
            if ($segment === '.' || $segment === '') {
                continue;
            }
            if ($segment === '..') {
                array_pop($clean);
            } else {
                $clean[] = $segment;
            }
        }
        return strtolower($url['scheme']).'://'.strtolower($url['host'])
            .(isset($url['port']) ? ':'.$url['port'] : '').'/'.implode('/', $clean)
            .(isset($url['query']) ? '?'.$url['query'] : '');
    }

    /** @return array{name:string,description:string,sku:string,price:int,price_status:string,images:list<string>,options:list<array{name:string,value:string}>,seo_title:string,seo_description:string} */
    public function product(string $html, string $url): array
    {
        $structured = $this->productJsonLd($html);
        $name = $this->text((string) ($structured['name'] ?? ''));
        if ($name === '') {
            $name = $this->text($this->meta($html, 'og:title'));
        }
        if ($name === '' && preg_match('~<h1\b[^>]*>(.*?)</h1>~is', $html, $matches)) {
            $name = $this->text($matches[1]);
        }
        $description = $this->text((string) ($structured['description'] ?? ''));
        if ($description === '') {
            $description = $this->text($this->meta($html, 'description'));
        }
        $seoTitle = $this->text($this->meta($html, 'og:title'));
        $seoDescription = $this->text($this->meta($html, 'description'));
        $sku = $this->text((string) ($structured['sku'] ?? ''));
        $images = [];
        $rawImages = $structured['image'] ?? [];
        if (is_string($rawImages)) {
            $rawImages = [$rawImages];
        }
        if (is_array($rawImages)) {
            foreach ($rawImages as $image) {
                $src = is_array($image) ? ($image['url'] ?? $image['contentUrl'] ?? null) : $image;
                if (is_string($src)) {
                    $resolved = $this->absoluteUrl($src, $url);
                    if ($resolved) {
                        $images[] = $resolved;
                    }
                }
            }
        }
        if ($images === []) {
            $ogImage = $this->meta($html, 'og:image');
            $resolved = $this->absoluteUrl($ogImage, $url);
            if ($resolved !== null) {
                $images[] = $resolved;
            }
        }
        if ($images === []) {
            preg_match_all('~<(?:img|a)\b[^>]*(?:data-zoom-image|data-large-image|data-src)\s*=\s*["\']([^"\']+)["\'][^>]*>~i', $html, $matches);
            foreach ($matches[1] as $src) {
                $resolved = $this->absoluteUrl(html_entity_decode($src, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($resolved !== null) {
                    $images[] = $resolved;
                }
            }
        }
        $images = array_slice(array_values(array_unique($images)), 0, 20);
        $price = $this->priceFromLd($structured);
        if ($price === 0) {
            $currency = $this->meta($html, 'product:price:currency');
            if ($currency === 'BRL') {
                $price = $this->brlToCents($this->meta($html, 'product:price:amount'));
            }
        }
        return [
            'name' => mb_substr($name, 0, 255),
            'description' => mb_substr($description, 0, 12000),
            'sku' => mb_substr($sku, 0, 255),
            'price' => $price,
            'price_status' => $price > 0 ? 'preco_minimo_confirmado' : 'preco_nao_exibido_na_fonte',
            'images' => $images,
            'options' => $this->options($html),
            'seo_title' => mb_substr($seoTitle, 0, 255),
            'seo_description' => mb_substr($seoDescription, 0, 1000),
        ];
    }

    private function text(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    }

    private function meta(string $html, string $key): string
    {
        preg_match_all('~<meta\s+[^>]*>~i', $html, $matches);
        foreach ($matches[0] as $tag) {
            $property = $this->attribute($tag, 'property') ?: $this->attribute($tag, 'name');
            if (strcasecmp($property, $key) === 0) {
                return html_entity_decode($this->attribute($tag, 'content'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return '';
    }

    private function attribute(string $tag, string $name): string
    {
        $regex = '~\b'.preg_quote($name, '~').'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))~i';
        if (preg_match($regex, $tag, $match)) {
            return (string) ($match[1] ?: ($match[2] ?: ($match[3] ?? '')));
        }
        return '';
    }

    /** @return array<string,mixed> */
    private function productJsonLd(string $html): array
    {
        preg_match_all('~<script\b[^>]*type\s*=\s*["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $matches);
        foreach ($matches[1] as $json) {
            $tree = json_decode(trim($json), true);
            if (is_array($tree)) {
                $found = $this->findProductObject($tree);
                if ($found !== []) {
                    return $found;
                }
            }
        }
        return [];
    }

    /** @param array<mixed> $data @return array<string,mixed> */
    private function findProductObject(array $data): array
    {
        $type = $data['@type'] ?? null;
        if ($type === 'Product' || (is_array($type) && in_array('Product', $type, true))) {
            return $data;
        }
        foreach ($data as $entry) {
            if (is_array($entry)) {
                $found = $this->findProductObject($entry);
                if ($found !== []) {
                    return $found;
                }
            }
        }
        return [];
    }

    private function priceFromLd(array $data): int
    {
        $offers = $data['offers'] ?? null;
        if ($offers === null) {
            return 0;
        }
        $items = is_array($offers) && array_is_list($offers) ? $offers : [$offers];
        $prices = [];
        foreach ($items as $offer) {
            if (! is_array($offer) || ($offer['priceCurrency'] ?? null) !== 'BRL') {
                continue;
            }
            $value = $offer['lowPrice'] ?? $offer['price'] ?? null;
            if ($value !== null) {
                $cents = $this->brlToCents((string) $value);
                if ($cents > 0) {
                    $prices[] = $cents;
                }
            }
        }
        return $prices === [] ? 0 : min($prices);
    }

    /** Decimal da fonte, convertido sem float. Ex.: 1.299,90 ou 1299.90 */
    private function brlToCents(string $value): int
    {
        $value = preg_replace('/[^\d,.]/', '', $value) ?? '';
        if ($value === '') {
            return 0;
        }
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1) {
            return 0;
        }
        if (! preg_match('/^(\d{1,12})(?:\.(\d{1,2}))?$/', $value, $parts)) {
            return 0;
        }
        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '0', 2, '0');
    }

    /** Opções publicadas, NÃO combinações vendáveis. DOM é opcional para ambientes php-xml. */
    private function options(string $html): array
    {
        if (! class_exists(\DOMDocument::class)) {
            return [];
        }
        $dom = new \DOMDocument();
        $old = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);
            $options = [];
            foreach ($xpath->query('//select[@name]') ?: [] as $select) {
                $name = $this->optionLabel($select->getAttribute('name'));
                if ($name === null) {
                    continue;
                }
                foreach ($xpath->query('.//option', $select) ?: [] as $option) {
                    $value = trim($option->textContent);
                    if ($value !== '' && ! preg_match('/^(selecione|escolha)/ui', $value)) {
                        $options[$name.'|'.$value] = ['name' => $name, 'value' => $value];
                    }
                }
            }
            foreach ($xpath->query('//input[@type="radio" and @name]') ?: [] as $input) {
                $name = $this->optionLabel($input->getAttribute('name'));
                if ($name === null) {
                    continue;
                }
                $value = trim($input->getAttribute('value'));
                if ($value === '') {
                    continue;
                }
                if ($input->hasAttribute('id') && preg_match('/^[a-zA-Z0-9_-]+$/', $input->getAttribute('id'))) {
                    $id = $input->getAttribute('id');
                    $label = $xpath->query('//label[@for="'.$id.'"]')->item(0);
                    if ($label !== null && trim($label->textContent) !== '') {
                        $value = trim($label->textContent);
                    }
                }
                $options[$name.'|'.$value] = ['name' => $name, 'value' => mb_substr($value, 0, 255)];
            }
            return array_slice(array_values($options), 0, 250);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($old);
        }
    }

    private function optionLabel(string $inputName): ?string
    {
        if (preg_match('/(cor|color)/i', $inputName)) {
            return 'Cor';
        }
        if (preg_match('/(tamanho|size|volume|embalagem|peso)/i', $inputName)) {
            return 'Tamanho';
        }
        return null;
    }
}
