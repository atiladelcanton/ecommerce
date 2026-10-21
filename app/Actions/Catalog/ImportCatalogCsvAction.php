<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Infrastructure\Catalog\CatalogCsv;
use App\Infrastructure\Catalog\RemoteCatalogImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/** Importação administrativa controlada de rascunhos de catálogo. */
final class ImportCatalogCsvAction
{
    public function __construct(
        private readonly CatalogCsv $csv,
        private readonly RemoteCatalogImage $downloader,
    ) {}

    /**
     * @param array{images?:?string,options?:?string,dry_run?:bool,update_text?:bool,no_images?:bool} $parameters
     * @param callable(string):void|null $logger
     * @return array<string,int>
     */
    public function execute(string $productsCsv, array $parameters = [], ?callable $logger = null): array
    {
        $products = $this->csv->read($productsCsv);
        $images = $this->readGrouped($parameters['images'] ?? null, 'product_slug');
        $options = $this->readGrouped($parameters['options'] ?? null, 'product_slug');
        $dryRun = (bool) ($parameters['dry_run'] ?? false);
        $noImages = (bool) ($parameters['no_images'] ?? false);
        $updateText = (bool) ($parameters['update_text'] ?? false);
        $log = $logger ?? static function (string $line): void {};
        $statistics = [
            'read' => count($products), 'created' => 0, 'existing' => 0,
            'categories_ok' => 0, 'images_saved' => 0, 'images_existing' => 0,
            'images_failed' => 0, 'options_staged' => 0, 'failed' => 0,
        ];
        $categories = DB::table('categories')->get(['id', 'name', 'slug'])->all();
        if ($categories === []) {
            throw new RuntimeException('Não existem categorias no banco. Cadastre as categorias antes da importação.');
        }
        $seenSources = [];

        foreach ($products as $rowNumber => $row) {
            try {
                $slug = $this->required($row, 'slug');
                $name = $this->required($row, 'name');
                if (mb_strlen($name) > 255 || mb_strlen($slug) > 255 || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                    throw new InvalidArgumentException('Nome ou slug inválido');
                }
                $sourceUrl = $this->required($row, 'source_url');
                $sourceHost = strtolower((string) parse_url($sourceUrl, PHP_URL_HOST));
                if ($sourceHost === '' || ! in_array(strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                    throw new InvalidArgumentException('URL de origem inválida');
                }
                $sourceKey = hash('sha256', $this->normalizeSourceUrl($sourceUrl));
                if (isset($seenSources[$sourceKey])) {
                    throw new InvalidArgumentException('URL de origem repetida no CSV');
                }
                $seenSources[$sourceKey] = true;
                $category = $this->resolveCategory($row, $categories);
                $statistics['categories_ok']++;
                $price = $this->money($row['price'] ?? '0');
                $sku = trim($row['sku'] ?? '');
                $metadata = [
                    'source_sku' => $sku ?: null,
                    'source_product_id' => $row['source_product_id'] ?? null,
                    'source_category_url' => $row['source_category_url'] ?? null,
                    'source_options_count' => $row['source_options_count'] ?? null,
                    'source_options_json' => json_decode($row['source_options_json'] ?? 'null', true),
                    'source_min_price_brl' => $row['source_min_price_brl'] ?? null,
                    'source_regular_price_brl' => $row['source_regular_price_brl'] ?? null,
                    'crawler_date' => $row['crawler_date'] ?? null,
                    'imported_reference_price_cents' => $price,
                    'needs_price_review' => true,
                ];

                if ($dryRun) {
                    $log("[SIMULAÇÃO] {$slug} -> categoria #{$category->id} ({$category->name}); preço ref.: {$price} centavos");
                    continue;
                }

                $operation = Cache::lock('saba-catalog-import:'.substr($sourceKey, 0, 40), 120)->block(15, fn (): array => DB::transaction(function () use ($row, $sku, $slug, $name, $sourceKey, $sourceUrl, $sourceHost, $category, $price, $metadata, $updateText, $options): array {
                    $existingSource = DB::table('catalog_import_sources')->where('source_key', $sourceKey)->lockForUpdate()->first();
                    $productId = $existingSource?->product_id;
                    $created = false;
                    if ($productId === null) {
                        $newSlug = $slug;
                        // Um slug existente sem vínculo com esta fonte não deve ser sequestrado.
                        if (DB::table('products')->where('slug', $newSlug)->exists()) {
                            $newSlug = substr($slug, 0, 225).'-import-'.substr($sourceKey, 0, 10);
                        }
                        $storeSku = $sku !== '' && ! DB::table('products')->where('sku', $sku)->exists() ? $sku : null;
                        $productId = DB::table('products')->insertGetId([
                            'category_id' => $category->id,
                            'name' => $name,
                            'slug' => $newSlug,
                            'sku' => $storeSku,
                            'short_description' => $this->text($row['short_description'] ?? '', 5000),
                            'description' => $this->text($row['description'] ?? '', 20000),
                            'seo_title' => $this->text($row['seo_title'] ?? '', 255),
                            'seo_description' => $this->text($row['seo_description'] ?? '', 1000),
                            'youtube_url' => null,
                            'availability' => 'unavailable',
                            'cost_price' => 0,
                            // 0 = referência ausente: somente em rascunhos inativos.
                            'price' => $price,
                            'promotional_price' => null,
                            'stock' => 0,
                            'weight' => null, 'height' => null, 'width' => null, 'length' => null,
                            'is_active' => false, 'is_featured' => false,
                            'whatsapp_quote_enabled' => true,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $created = true;
                    } elseif ($updateText) {
                        $existing = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                        if ($existing && ! $existing->is_active) {
                            // Protege preços, saldos, SKU, status e dimensões modificados pela equipe.
                            DB::table('products')->where('id', $productId)->update([
                                'name' => $name,
                                'short_description' => $this->text($row['short_description'] ?? '', 5000),
                                'description' => $this->text($row['description'] ?? '', 20000),
                                'seo_title' => $this->text($row['seo_title'] ?? '', 255),
                                'seo_description' => $this->text($row['seo_description'] ?? '', 1000),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    if ($existingSource === null) {
                        $sourceId = DB::table('catalog_import_sources')->insertGetId([
                            'product_id' => $productId, 'source_key' => $sourceKey,
                            'source_url' => $sourceUrl, 'source_domain' => $sourceHost,
                            'price_status' => $row['price_status'] ?? null,
                            'variant_details_status' => $row['variant_details_status'] ?? null,
                            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    } else {
                        $sourceId = $existingSource->id;
                        DB::table('catalog_import_sources')->where('id', $sourceId)->update([
                            'price_status' => $row['price_status'] ?? null,
                            'variant_details_status' => $row['variant_details_status'] ?? null,
                            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                            'updated_at' => now(),
                        ]);
                    }

                    $stageCount = 0;
                    foreach ($options[$slug] ?? [] as $index => $option) {
                        $optionName = trim($option['option_name'] ?? '');
                        $optionValue = trim($option['option_value'] ?? '');
                        if ($optionName === '' || $optionValue === '') {
                            continue; // "opções não recuperadas" não é uma variação válida.
                        }
                        if (mb_strlen($optionName) > 120 || mb_strlen($optionValue) > 255) {
                            continue;
                        }
                        $stageCount += DB::table('catalog_import_options')->insertOrIgnore([
                            'catalog_import_source_id' => $sourceId,
                            'name' => $optionName,
                            'value' => $optionValue,
                            'status' => 'unverified',
                            'sort_order' => $index,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    return ['product_id' => $productId, 'created' => $created, 'staged' => $stageCount];
                }, 3));

                $statistics[$operation['created'] ? 'created' : 'existing']++;
                $statistics['options_staged'] += $operation['staged'];
                if ($noImages) {
                    continue;
                }
                $productImages = $images[$slug] ?? [];
                if ($productImages === [] && trim($row['image_url'] ?? '') !== '') {
                    $productImages[] = ['image_url' => $row['image_url'], 'alt_text' => $name, 'sort_order' => '0', 'is_primary' => '1'];
                }
                usort($productImages, static fn (array $a, array $b): int => ((int) ($b['is_primary'] ?? 0) <=> (int) ($a['is_primary'] ?? 0)) ?: ((int) ($a['sort_order'] ?? 0) <=> (int) ($b['sort_order'] ?? 0)));
                foreach ($productImages as $image) {
                    if (trim($image['image_url'] ?? '') === '') {
                        continue;
                    }
                    try {
                        $path = $this->downloader->download($image['image_url'], $sourceHost, $slug);
                        $linked = DB::transaction(function () use ($path, $image, $operation): bool {
                            $productId = $operation['product_id'];
                            // Serialize inclusão de imagens para não criar duas principais.
                            DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                            if (DB::table('product_images')->where('product_id', $productId)->where('path', $path)->exists()) {
                                return false;
                            }
                            $hasPrimary = DB::table('product_images')->where('product_id', $productId)->where('is_primary', true)->exists();
                            DB::table('product_images')->insert([
                                'product_id' => $productId, 'path' => $path,
                                'alt_text' => $this->text($image['alt_text'] ?? '', 255) ?: null,
                                'sort_order' => max(0, (int) ($image['sort_order'] ?? 0)),
                                'is_primary' => ! $hasPrimary,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                            return true;
                        }, 3);
                        $statistics[$linked ? 'images_saved' : 'images_existing']++;
                    } catch (\Throwable $exception) {
                        $statistics['images_failed']++;
                        $log("[IMAGEM FALHOU] {$slug}: {$exception->getMessage()}");
                    }
                }
                $log(($operation['created'] ? '[CRIADO] ' : '[EXISTENTE] ')."{$slug}, categoria #{$category->id}, produto #{$operation['product_id']}");
            } catch (\Throwable $exception) {
                $statistics['failed']++;
                $log('[ERRO linha '.($rowNumber + 2).'] '.($row['slug'] ?? '?').': '.$exception->getMessage());
            }
        }

        return $statistics;
    }

    /** @return array<string,list<array<string,string>>> */
    private function readGrouped(?string $file, string $group): array
    {
        $values = [];
        if ($file === null || $file === '') {
            return $values;
        }
        foreach ($this->csv->read($file) as $row) {
            $values[$row[$group] ?? ''][] = $row;
        }
        return $values;
    }

    private function required(array $row, string $column): string
    {
        $value = trim($row[$column] ?? '');
        if ($value === '') {
            throw new InvalidArgumentException("Campo obrigatório ausente: {$column}");
        }
        return $value;
    }

    /** @param list<object> $categories */
    private function resolveCategory(array $row, array $categories): object
    {
        $wantedId = ($row['category_id'] ?? '') !== '' ? (int) $row['category_id'] : null;
        $wantedName = trim($row['category_name'] ?? '');
        $wantedSlug = trim($row['category_slug'] ?? '');
        // Não se basear só no número do CSV: os IDs podem divergir entre ambientes.
        foreach ($categories as $category) {
            if ($wantedSlug !== '' && $category->slug === $wantedSlug) {
                return $category;
            }
        }
        if ($wantedName !== '') {
            foreach ($categories as $category) {
                if (Str::slug($category->name) === Str::slug($wantedName)) {
                    return $category;
                }
            }
        }
        if ($wantedId !== null) {
            foreach ($categories as $category) {
                if ((int) $category->id === $wantedId
                    && ($wantedName === '' || Str::slug($category->name) === Str::slug($wantedName))) {
                    return $category;
                }
            }
        }
        throw new InvalidArgumentException('Categoria não encontrada ou incompatível: id='.($wantedId ?? '').", nome={$wantedName}, slug={$wantedSlug}");
    }

    private function money(string $value): int
    {
        if ($value === '' || ! ctype_digit($value)) {
            throw new InvalidArgumentException('Preço deve estar em centavos inteiros, sem símbolos: '.$value);
        }
        return (int) $value;
    }

    private function text(string $value, int $max): ?string
    {
        $value = trim(strip_tags($value)); // nunca importar HTML remoto como HTML confiável
        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function normalizeSourceUrl(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = rtrim($parts['path'] ?? '/', '/') ?: '/';
        // Parâmetros de identidade do produto devem ser mantidos; remover apenas tracking.
        $parameters = [];
        parse_str($parts['query'] ?? '', $parameters);
        foreach (array_keys($parameters) as $key) {
            if (str_starts_with(strtolower($key), 'utm_') || in_array(strtolower($key), ['fbclid', 'gclid', 'msclkid'], true)) {
                unset($parameters[$key]);
            }
        }
        ksort($parameters);
        $query = $parameters !== [] ? '?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986) : '';
        return $host.$path.$query;
    }
}
