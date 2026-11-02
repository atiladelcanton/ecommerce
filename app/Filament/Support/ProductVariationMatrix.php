<?php

declare(strict_types=1);

namespace App\Filament\Support;

use InvalidArgumentException;

/**
 * Presentation-only adapter for the Shopee-style variation editor.
 * The actual combination validation, inventory and persistence remain in
 * SyncProductVariantsAction. Neither the database nor storefront is changed.
 */
final class ProductVariationMatrix
{
    public const MAX_AXES = 5;
    public const MAX_ROWS = 150;

    /** @return list<array{name:string, values:list<string>}> */
    public static function axesFromRows(array $rows): array
    {
        $axes = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach (($row['attributes'] ?? []) as $name => $value) {
                $name = trim((string) $name);
                $value = trim((string) $value);
                if ($name === '' || $value === '') {
                    continue;
                }
                $key = mb_strtolower($name);
                if (! isset($axes[$key])) {
                    $axes[$key] = ['name' => $name, 'values' => []];
                }
                if (! in_array(mb_strtolower($value), array_map('mb_strtolower', $axes[$key]['values']), true)) {
                    $axes[$key]['values'][] = $value;
                }
            }
        }

        return array_values($axes);
    }

    /**
     * Prevent a misleading save if an editor changed the types/tags but forgot
     * to press "Gerar combinações". Sparse existing matrices remain valid.
     */
    public static function axesMatchRows(array $axes, array $rows): bool
    {
        $expected = self::axesFromRows($rows);
        if (count($axes) !== count($expected)) {
            return false;
        }
        $normalize = static function (array $items): array {
            $normalized = [];
            foreach ($items as $axis) {
                $name = mb_strtolower(trim((string) ($axis['name'] ?? '')));
                if ($name === '' || isset($normalized[$name])) {
                    return [];
                }
                $values = array_map(static fn ($value): string => mb_strtolower(trim((string) $value)), $axis['values'] ?? []);
                if (in_array('', $values, true) || count(array_unique($values)) !== count($values)) {
                    return [];
                }
                sort($values);
                $normalized[$name] = $values;
            }
            ksort($normalized);

            return $normalized;
        };

        return $normalize(array_values($axes)) === $normalize($expected);
    }

    /**
     * @param array<array-key, array<string, mixed>> $axes
     * @param array<array-key, array<string, mixed>> $currentRows
     * @return list<array<string, mixed>>
     */
    public static function generate(array $axes, array $currentRows): array
    {
        // No characteristics means a simple product, with no selectable variations.
        if ($axes === []) {
            return [];
        }
        if (count($axes) > self::MAX_AXES) {
            throw new InvalidArgumentException('É possível cadastrar até '.self::MAX_AXES.' tipos de variação.');
        }

        $prepared = [];
        $seenNames = [];
        $total = 1;
        foreach (array_values($axes) as $index => $axis) {
            $name = trim((string) ($axis['name'] ?? ''));
            if ($name === '' || mb_strlen($name) > 120) {
                throw new InvalidArgumentException('Informe o nome da característica '.($index + 1).' (até 120 caracteres).');
            }
            $key = mb_strtolower($name);
            if (isset($seenNames[$key])) {
                throw new InvalidArgumentException('A característica "'.$name.'" está repetida. Use nomes diferentes.');
            }
            $seenNames[$key] = true;
            $values = [];
            $seenValues = [];
            foreach (($axis['values'] ?? []) as $value) {
                $value = trim((string) $value);
                if ($value === '') {
                    continue;
                }
                if (mb_strlen($value) > 120) {
                    throw new InvalidArgumentException('Um dos valores de "'.$name.'" ultrapassa 120 caracteres.');
                }
                $valueKey = mb_strtolower($value);
                if (isset($seenValues[$valueKey])) {
                    throw new InvalidArgumentException('O valor "'.$value.'" está repetido em "'.$name.'".');
                }
                $seenValues[$valueKey] = true;
                $values[] = $value;
            }
            if ($values === []) {
                throw new InvalidArgumentException('Adicione pelo menos um valor em "'.$name.'" e pressione Enter.');
            }
            $total *= count($values);
            if ($total > self::MAX_ROWS) {
                throw new InvalidArgumentException('Essas opções geram '.$total.' combinações. O limite é '.self::MAX_ROWS.'. Reduza os valores.');
            }
            $prepared[] = ['name' => $name, 'values' => $values];
        }

        $previous = [];
        foreach ($currentRows as $row) {
            if (is_array($row) && is_array($row['attributes'] ?? null)) {
                $previous[self::key($row['attributes'])] ??= $row;
            }
        }

        $combinations = [[]];
        foreach ($prepared as $axis) {
            $next = [];
            foreach ($combinations as $attributes) {
                foreach ($axis['values'] as $value) {
                    $next[] = array_merge($attributes, [$axis['name'] => $value]);
                }
            }
            $combinations = $next;
        }

        $result = [];
        foreach ($combinations as $attributes) {
            if (mb_strlen(implode(' / ', array_keys($attributes))) > 255 || mb_strlen(implode(' / ', array_values($attributes))) > 255) {
                throw new InvalidArgumentException('Resuma os nomes ou valores das variações para caber no cadastro.');
            }
            $existing = $previous[self::key($attributes)] ?? [];
            $result[] = array_merge([
                'id' => null, 'sku' => null, 'price' => null, 'promotional_price' => null,
                'stock' => null, 'weight' => null, 'height' => null, 'width' => null,
                'length' => null, 'is_active' => true,
            ], $existing, [
                'attributes' => $attributes,
                'combination_display' => implode(' / ', array_values($attributes)),
            ]);
        }

        return $result;
    }

    public static function displayRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            $row['combination_display'] = implode(' / ', array_values($row['attributes'] ?? []));

            return $row;
        }, $rows);
    }

    private static function key(array $attributes): string
    {
        $canonical = [];
        foreach ($attributes as $name => $value) {
            $canonical[mb_strtolower(trim((string) $name))] = mb_strtolower(trim((string) $value));
        }
        ksort($canonical);

        return hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
