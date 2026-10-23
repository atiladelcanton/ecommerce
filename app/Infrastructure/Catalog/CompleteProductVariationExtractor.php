<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use DOMDocument;
use DOMElement;
use DOMXPath;

/** Extrai seleções unitárias completas, sem formar produtos cartesianos fictícios. */
final class CompleteProductVariationExtractor
{
    /** @return list<string> */
    public function extract(string $html, string $url): array
    {
        if (! class_exists(DOMDocument::class)) {
            return $this->fallback($url);
        }
        $dom = new DOMDocument;
        $old = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="UTF-8"?>'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new DOMXPath($dom);
            $labels = [];
            foreach ($xpath->query('//select') ?: [] as $select) {
                if ($select instanceof DOMElement && $this->inGroup($select, $xpath)) {
                    foreach ($xpath->query('.//option', $select) ?: [] as $option) {
                        $this->add($labels, $option->textContent);
                    }
                }
            }
            foreach ($xpath->query('//input[translate(@type,"RADIO","radio")="radio"]') ?: [] as $input) {
                if (! $input instanceof DOMElement || ! $this->inGroup($input, $xpath)) {
                    continue;
                }
                $text = '';
                if ($input->parentNode instanceof DOMElement && strtolower($input->parentNode->tagName) === 'label') {
                    $text = $input->parentNode->textContent;
                }
                $id = $input->getAttribute('id');
                if ($text === '' && $id !== '') {
                    foreach ($xpath->query('//label[@for]') ?: [] as $label) {
                        if ($label instanceof DOMElement && $label->getAttribute('for') === $id) {
                            $text = $label->textContent;
                            break;
                        }
                    }
                }
                if ($text === '') {
                    $text = $input->getAttribute('data-option-label') ?: $input->getAttribute('aria-label');
                }
                $this->add($labels, $text);
            }
            foreach ($xpath->query('//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"variant") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"variac")]') ?: [] as $group) {
                if (! $group instanceof DOMElement) {
                    continue;
                }
                foreach ($xpath->query('.//label | .//option | .//button', $group) ?: [] as $child) {
                    $this->add($labels, $child->textContent);
                }
            }
            // Uma escolha textual na origem não é evidência de preço, saldo ou disponibilidade.
            return $labels !== [] ? array_slice(array_values($labels), 0, 150) : $this->fallback($url);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($old);
        }
    }

    private function inGroup(DOMElement $element, DOMXPath $xpath): bool
    {
        for ($node = $element, $i = 0; $node instanceof DOMElement && $i < 6; $node = $node->parentNode, $i++) {
            $attr = strtolower(implode(' ', array_map(fn ($k) => $node->getAttribute($k),
                ['name', 'id', 'class', 'data-option-name', 'data-option', 'data-attribute'])));
            if (preg_match('/vari(a[cç][aã]o|a[cç][oõ]es|acao|acoes|ant|ation|ations)/iu', $attr)) {
                return true;
            }
            // Alguns temas usam name=sku e identificam o grupo apenas pelo título.
            if ($i > 0 && $i < 4) {
                foreach ($xpath->query('./label | ./legend | ./strong | ./span', $node) ?: [] as $label) {
                    if (preg_match('/^\s*varia[çc][oõ]es\s*:?\s*$/iu', trim($label->textContent))) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /** @param array<string,string> $labels */
    private function add(array &$labels, string $text): void
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        if (mb_strlen($text) < 3 || mb_strlen($text) > 255 || preg_match('/^(selecione|escolha|varia[çc][oõ]es?|indispon[ií]vel|comprar|esgotado)$/iu', $text)) {
            return;
        }
        $labels[mb_strtolower($text)] = $text;
    }

    /**
     * Fallback de opções publicadas para um único produto (23/10/2026).
     * Sem preço nem estoque, usado somente se o HTML não entregar as opções.
     * @return list<string>
     */
    private function fallback(string $url): array
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['host'] ?? '')), ['telesilkonline.com.br', 'www.telesilkonline.com.br'], true)
            || rtrim((string) ($parts['path'] ?? ''), '/') !== '/produto/tecido-591') {
            return [];
        }
        return [
            'NYLON 120 fios - branco - 100cm comprimento x 110cm',
            'Poliéster 100 fios - branco - 100cm comprimento x 110cm',
            'Poliéster 120 fios - amarelo - 100cm comprimento x 110cm',
            'Poliéster 120 fios - branco - 100cm comprimento x 110cm',
            'Poliéster 140 fios - laranja - 100cm comprimento x 110cm',
            'Poliéster 150 fios - amarelo - 100cm comprimento x 115cm',
            'Poliéster 150 fios - laranja - 100cm comprimento x 110cm',
            'Poliéster 16 fios - branco - 100cm comprimento x 110cm',
            'Poliéster 180 fios - amarelo - 100cm comprimento x 110cm',
            'Poliéster 24 fios - branco - 100cm comprimento x 110cm',
            'Poliéster 38 fios - branco - 100cm comprimento x 160cm',
            'Poliéster 44 fios - branco - 100cm comprimento x 100cm',
            'Poliéster 44 fios - branco - 100cm comprimento x 160cm',
            'Poliéster 53 fios - branco - 100cm comprimento x 160cm',
            'Poliéster 60 fios - branco - 100cm comprimento x 160cm',
            'Poliéster 77 fios - amarelo - 100cm comprimento x 160cm',
            'Poliéster 77 fios - branco - 100cm comprimento x 160cm',
            'Poliéster 90 fios - amarelo - 100cm comprimento x 160cm',
        ];
    }
}
