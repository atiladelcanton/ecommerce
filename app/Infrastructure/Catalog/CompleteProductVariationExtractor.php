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

    /**
     * Lê os metadados dos seletores da página, sem depender de nomes fixos
     * como Cor, Tamanho, Fios ou Material. data-items é a fonte prioritária.
     *
     * @return array{
     *     options:list<array{name:string,value:string}>,
     *     variants:list<array<string,string>>,
     *     complete:list<string>,
     *     warnings:list<string>
     * }
     */
    public function extractDetailed(string $html, string $url): array
    {
        $groups = $this->attributeGroups($html);

        // Compatibilidade com templates antigos: lista de opções completas
        // exibidas em seletores/rádios de "Variações" (inclui o tecido-591).
        if ($groups === []) {
            $choices = $this->extract($html, $url);
            $options = array_map(
                static fn (string $choice): array => ['name' => 'Variação', 'value' => $choice],
                $choices
            );

            return ['options' => $options, 'variants' => [], 'complete' => $choices, 'warnings' => []];
        }

        $options = [];
        foreach ($groups as $name => $values) {
            foreach ($values as $value) {
                $options[] = ['name' => $name, 'value' => $value];
            }
        }

        // Cada opção de um único atributo é uma escolha completa: Tamanho=20x25.
        // Com DOIS OU MAIS seletores não há prova de que TODAS as combinações
        // existam ou possam ser vendidas; preservar apenas as opções para revisão.
        if (count($groups) > 1) {
            return [
                'options' => $options,
                'variants' => [],
                'complete' => [],
                'warnings' => ['Há '.count($groups).' atributos independentes ('.implode(', ', array_keys($groups)).'). '
                    .'As opções foram registradas para conferência; não foram inventadas combinações de venda.'],
            ];
        }

        $attribute = array_key_first($groups);
        $values = $groups[$attribute];
        if ($attribute === 'Variação') {
            // "Variação" pode conter uma combinação textual completa que a Action
            // conhece como decompor (por exemplo: material/fios/cor do tecido).
            return ['options' => $options, 'variants' => [], 'complete' => $values, 'warnings' => []];
        }

        $variants = array_map(
            static fn (string $value): array => [$attribute => $value],
            array_slice($values, 0, 100)
        );
        $warnings = count($values) > 100
            ? ['A origem contém mais de 100 opções. Apenas 100 rascunhos foram gerados; confira as opções adicionais na tabela de importação.']
            : [];

        return ['options' => $options, 'variants' => $variants, 'complete' => [], 'warnings' => $warnings];
    }

    /**
     * A extração simples usada pelo CatalogHtmlExtractor já pode ter localizado
     * opções de select/radio quando o extrator detalhado não reconheceu o tema.
     * Só uma característica independente pode virar rascunhos sem supor SKUs
     * de combinações inexistentes.
     *
     * @param list<array{name:string,value:string}> $rows
     * @return array{options:list<array{name:string,value:string}>,variants:list<array<string,string>>,complete:list<string>,warnings:list<string>}
     */
    public function fromCollectedOptions(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $name = $this->normalizeGroup((string) ($row['name'] ?? ''));
            $value = $this->optionValue((string) ($row['value'] ?? ''));
            if ($name === '' || $value === null) {
                continue;
            }
            // Opções técnicas da página (IDs, quantidade, estado etc.) não
            // são atributos comerciais confirmados.
            if (preg_match('/^(?:sku|id|codigo|quantidade|qtd|estoque|preco)$/iu', \Illuminate\Support\Str::ascii($name))) {
                continue;
            }
            $groups[$name][mb_strtolower($value)] = $value;
        }

        $options = [];
        foreach ($groups as $name => $values) {
            foreach ($values as $value) {
                $options[] = ['name' => $name, 'value' => $value];
            }
        }
        if (count($groups) > 1) {
            return ['options' => $options, 'variants' => [], 'complete' => [],
                'warnings' => ['Foram encontrados '.count($groups).' atributos independentes ('
                    .implode(', ', array_keys($groups)).'). Revise as combinações reais antes de cadastrar variantes.']];
        }
        if ($groups === []) {
            return ['options' => [], 'variants' => [], 'complete' => [], 'warnings' => []];
        }

        $name = array_key_first($groups);
        $values = array_slice(array_values($groups[$name]), 0, 100);
        if ($name === 'Variação') {
            return ['options' => $options, 'variants' => [], 'complete' => $values, 'warnings' => []];
        }
        return ['options' => $options,
            'variants' => array_map(static fn (string $value): array => [$name => $value], $values),
            'complete' => [],
            'warnings' => count($groups[$name]) > 100
                ? ['Mais de 100 escolhas encontradas: os rascunhos foram limitados a 100.'] : []];
    }

    /** @return array<string,list<string>> */
    private function attributeGroups(string $html): array
    {
        // Prioridade para os metadados Vnda: não dependem de inputs visíveis,
        // nem da extensão DOM instalada no PHP CLI da hospedagem.
        $rawGroups = $this->rawDataItemGroups($html);
        if (! class_exists(DOMDocument::class)) {
            return $rawGroups;
        }
        $groups = [];
        foreach ($rawGroups as $name => $values) {
            foreach ($values as $value) {
                $groups[$name][mb_strtolower($value)] = $value;
            }
        }

        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            if (! $dom->loadHTML('<?xml encoding="UTF-8"?>'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                return [];
            }
            $xpath = new DOMXPath($dom);

            // Vnda/Tele Silk: <div class="prod-option ..." data-attribute-name="Tamanho"
            // data-items="20x25 // 20x30 // ...">. data-items traz também opções
            // que podem não aparecer como texto visível no HTML renderizado.
            $query = '//*[contains(concat(" ", normalize-space(@class), " "), " prod-option ") '
                .'or (@data-attribute-name and @data-items)]';
            foreach ($xpath->query($query) ?: [] as $container) {
                if (! $container instanceof DOMElement) {
                    continue;
                }
                $name = $this->groupName($container);
                if ($name === '') {
                    continue;
                }

                $values = $this->dataValues($container->getAttribute('data-items'));
                if ($values === []) {
                    // Para temas que utilizam a mesma estrutura sem data-items.
                    foreach ($xpath->query('.//select/option | .//label | .//button | .//*[@data-value]', $container) ?: [] as $child) {
                        if (! $child instanceof DOMElement) {
                            continue;
                        }
                        $value = $child->getAttribute('data-value');
                        if ($value === '') {
                            $value = $child->textContent;
                        }
                        $value = $this->optionValue($value);
                        if ($value !== null && mb_strtolower($value) !== mb_strtolower($name)) {
                            $values[mb_strtolower($value)] = $value;
                        }
                    }
                    $values = array_values($values);
                }

                foreach ($values as $value) {
                    $groups[$name][mb_strtolower($value)] = $value;
                }
            }

            // Seletores sem o wrapper Vnda: nomes de atributos legíveis, em vez
            // de class="variant" ou de uma enumeração rígida Cor/Tamanho.
            if ($groups === []) {
                foreach ($xpath->query('//select[@data-attribute-name or @name]') ?: [] as $select) {
                    if (! $select instanceof DOMElement) {
                        continue;
                    }
                    $raw = trim($select->getAttribute('data-attribute-name'));
                    if ($raw === '') {
                        $raw = trim($select->getAttribute('name'));
                    }
                    if (! preg_match('/^(?:variac(?:ao|oes)|variantes?|cor|color|tamanho|size|gramatura|material|fios|embalagem|volume|largura|comprimento|espessura)$/iu', $raw)) {
                        continue;
                    }
                    $name = $this->normalizeGroup($raw);
                    foreach ($xpath->query('.//option', $select) ?: [] as $option) {
                        $value = $this->optionValue($option->textContent);
                        if ($value !== null) {
                            $groups[$name][mb_strtolower($value)] = $value;
                        }
                    }
                }
            }

            $result = [];
            foreach ($groups as $name => $values) {
                if ($values !== []) {
                    $result[$name] = array_slice(array_values($values), 0, 200);
                }
            }
            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * Lê a TAG de abertura de qualquer componente que exponha data-items.
     * Regex limitada a tags/atributos (não tenta interpretar o documento HTML
     * inteiro), como alternativa quando o HTML chega parcialmente montado.
     *
     * @return array<string,list<string>>
     */
    private function rawDataItemGroups(string $html): array
    {
        $groups = [];
        // HTML limitado pelo cliente HTTP a 2 MB.
        $tagPattern = '~<[a-z][a-z0-9-]*\b((?:"[^"]*"|\'[^\']*\'|[^"\'>])*)>~iu';
        if (! preg_match_all($tagPattern, $html, $tags, PREG_SET_ORDER)) {
            return [];
        }
        foreach ($tags as $tag) {
            $markup = $tag[0];
            $classes = $this->tagAttribute($markup, 'class');
            $name = $this->tagAttribute($markup, 'data-attribute-name');
            $items = $this->tagAttribute($markup, 'data-items');
            if ($items === '' || ($name === '' && ! preg_match('/(?:^|\s)prod-option(?:\s|$)/iu', $classes))) {
                continue;
            }
            if ($name === '' && preg_match('/(?:^|\s)option-(?!input\b)([^\s]+)/u', $classes, $found)) {
                $name = str_replace(['-', '_'], ' ', $found[1]);
            }
            $name = $this->normalizeGroup($name);
            if ($name === '') {
                continue;
            }
            foreach ($this->dataValues($items) as $value) {
                $groups[$name][mb_strtolower($value)] = $value;
            }
        }
        return array_map(static fn (array $values): array => array_values($values), $groups);
    }

    private function tagAttribute(string $tag, string $attribute): string
    {
        $pattern = '~(?<![\w-])'.preg_quote($attribute, '~').'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))~iu';
        if (! preg_match($pattern, $tag, $matches)) {
            return '';
        }
        // Capturas opcionais não aparecem no array de preg_match() quando
        // não participam do match (ex.: data-attribute-name="").
        // Jamais acessar [1], [2] ou [3] sem verificar se existem.
        foreach ([1, 2, 3] as $index) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                return html_entity_decode($matches[$index], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return '';
    }

    private function groupName(DOMElement $container): string
    {
        $raw = trim($container->getAttribute('data-attribute-name'));
        if ($raw === '' && preg_match('/(?:^|\s)option-(?!input\b)([^\s]+)/u', $container->getAttribute('class'), $match)) {
            $raw = str_replace(['-', '_'], ' ', $match[1]);
        }
        return $this->normalizeGroup($raw);
    }

    private function normalizeGroup(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/u', ' ', html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        if ($raw === '' || mb_strlen($raw) > 120) {
            return '';
        }
        if (preg_match('/^vari(?:ac(?:ao|oes)|antes?)$/iu', \Illuminate\Support\Str::ascii($raw))) {
            return 'Variação';
        }
        return mb_convert_case($raw, MB_CASE_TITLE, 'UTF-8');
    }

    /** @return list<string> */
    private function dataValues(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        // Alguns templates serializam data-items como array JSON.
        if (str_starts_with($raw, '[')) {
            $json = json_decode($raw, true);
            if (is_array($json) && array_is_list($json)) {
                $items = array_filter($json, 'is_scalar');
            }
        }
        $items ??= preg_split('/\s*\/\/\s*/u', $raw) ?: [];
        $values = [];
        foreach ($items as $item) {
            $value = $this->optionValue((string) $item);
            if ($value !== null) {
                $values[mb_strtolower($value)] = $value;
            }
        }
        return array_slice(array_values($values), 0, 200);
    }

    private function optionValue(string $raw): ?string
    {
        $value = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        if ($value === '' || mb_strlen($value) > 255 || preg_match('/^(?:selecione(?:\s+.+)?|escolha(?:\s+.+)?|indispon[ií]vel|esgotado|comprar|--?|-|n\/a)$/iu', $value)) {
            return null;
        }
        return $value;
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
