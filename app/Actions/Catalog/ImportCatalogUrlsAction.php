<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Infrastructure\Catalog\CatalogHtmlExtractor;
use App\Infrastructure\Catalog\HomologInkPageExtractor;
use App\Infrastructure\Catalog\RemoteCatalogImage;
use App\Infrastructure\Catalog\SafeCatalogHttp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Importação de páginas individuais, sem publicar automaticamente nenhum produto. */
final class ImportCatalogUrlsAction
{
    public function __construct(
        private readonly SafeCatalogHttp $http,
        private readonly CatalogHtmlExtractor $html,
        private readonly HomologInkPageExtractor $technical,
        private readonly RemoteCatalogImage $images,
    ) {}

    /**
     * @param list<string> $urls
     * @param array{category?:?string,dry_run?:bool,no_images?:bool,refresh_text?:bool,publish_preview?:bool,demo_price_cents?:?int} $settings
     * @param callable(string):void $log
     * @return array<string,int>
     */
    public function execute(array $urls, array $settings, callable $log): array
    {
        $urls = array_values(array_unique(array_filter(array_map('trim', $urls))));
        if ($urls === [] || count($urls) > 100) {
            throw new InvalidArgumentException('Informe entre 1 e 100 URLs de produtos por execução.');
        }
        $dryRun = (bool) ($settings['dry_run'] ?? false);
        $noImages = (bool) ($settings['no_images'] ?? false);
        $refreshText = (bool) ($settings['refresh_text'] ?? false);
        $forcedCategory = trim((string) ($settings['category'] ?? ''));
        $publishPreview = (bool) ($settings['publish_preview'] ?? false);
        $demoPrice = $settings['demo_price_cents'] ?? null;
        if ($publishPreview && ! app()->environment(['local', 'staging', 'homolog', 'homologation', 'testing'])) {
            throw new RuntimeException('Modo vitrine demonstrativa só pode ser usado com APP_ENV=staging/homolog/local/testing.');
        }
        if ($publishPreview && (! is_int($demoPrice) || $demoPrice < 1)) {
            throw new InvalidArgumentException('Use --demo-price-cents=9990 com --publish-preview.');
        }
        $stats = ['urls' => count($urls), 'created' => 0, 'existing' => 0, 'categories_created' => 0,
            'uncategorized' => 0, 'images' => 0, 'image_errors' => 0, 'options' => 0,
            'variant_drafts' => 0, 'preview_visible' => 0, 'failed' => 0];
        // As URLs dos produtos são públicas. Não alterar links técnicos de origem.
        foreach ($urls as $index => $url) {
            if ($index > 0 && ! app()->environment('testing')) usleep(600000);
            try {
                $this->http->assertPublicUrl($url);
                $source = $this->canonicalUrl($url);
                $key = hash('sha256', $source);
                $preset = $this->presetFor($source);
                $html = $this->http->get($url);
                $detail = $this->html->product($html, $url);
                $name = $this->clean((string) (($preset['name'] ?? '') ?: $detail['name']), 255);
                if ($name === '') {
                    throw new RuntimeException('Não foi possível identificar o nome do produto.');
                }
                $page = $this->technical->extract($html, $url, $name);
                $technicalDescription = $this->clean($page['description'], 20000);
                $fallbackDescription = $this->clean((string) ($detail['description'] ?: ($preset['summary'] ?? '')), 20000);
                $description = $technicalDescription !== '' ? $technicalDescription : $fallbackDescription;
                $short = $this->clean((string) ($preset['summary'] ?? strtok($description, "\n")), 1000);
                if ($short === '') {
                    $short = mb_substr($description, 0, 500);
                }
                $seo = $this->clean((string) ($detail['seo_description'] ?: $short), 1000);
                $categoryName = $forcedCategory !== '' ? $forcedCategory
                    : ($preset['category'] ?? $this->classify($name, $description));
                $fallback = $this->normalized($categoryName) === 'sem definicao';
                $slug = $this->slugFromUrl($source);
                $skuPrefix = $this->skuPrefix($categoryName);
                $sku = 'SABA-'.$skuPrefix.'-'.strtoupper(substr($key, 0, 12));
                $imageUrls = [];
                $imageDedup = [];
                foreach (array_merge($page['images'], $detail['images'], isset($preset['fallback_image']) ? [$preset['fallback_image']] : []) as $photo) {
                    if (! is_string($photo) || $photo === '') continue;
                    $photoKey = preg_replace('~/(?:150x|1200x|800x)/~', '/', (string) parse_url($photo, PHP_URL_PATH)) ?: $photo;
                    if (isset($imageDedup[$photoKey])) continue;
                    $imageDedup[$photoKey] = true;
                    $imageUrls[] = $photo;
                    if (count($imageUrls) >= 12) break;
                }
                $options = $this->options($preset, $detail['options']);
                $draftVariants = $this->draftVariants($preset);
                $log(sprintf('[COLETA] %s => %s; imagens: %d; opções: %d; %s', $name, $categoryName,
                    count($imageUrls), count($options), $technicalDescription !== '' ? '.short-description' : 'descrição alternativa'));
                if ($dryRun) {
                    $log('[DRY-RUN] Nenhuma alteração no banco ou em arquivos: '.$slug);
                    if ($fallback) {
                        $stats['uncategorized']++;
                    }
                    continue;
                }
                $record = DB::transaction(function () use ($categoryName, $fallback, $key, $source, $url, $name,
                    $slug, $sku, $short, $description, $seo, $options, $draftVariants, $refreshText, $detail, $publishPreview, $demoPrice, &$stats): array {
                    $category = $this->category($categoryName, $fallback);
                    // Contagem de categorias novas calculada apenas após commit.
                    $existingSource = DB::table('catalog_import_sources')->where('source_key', $key)->lockForUpdate()->first();
                    $existing = $existingSource ? DB::table('products')->where('id', $existingSource->product_id)->lockForUpdate()->first() : null;
                    $isNew = ! $existing;
                    if ($isNew) {
                        $realSlug = $slug;
                        // Uma URL nunca se apropria de um produto existente não vinculado à fonte.
                        if (DB::table('products')->where('slug', $realSlug)->exists()) {
                            $realSlug = mb_substr($slug, 0, 240).'-'.substr($key, 0, 10);
                        }
                        if (DB::table('products')->where('sku', $sku)->exists()) {
                            throw new RuntimeException('SKU em uso por outra origem: '.$sku);
                        }
                        $id = DB::table('products')->insertGetId([
                            'category_id' => $category['id'], 'name' => $name, 'slug' => $realSlug, 'sku' => $sku,
                            'short_description' => $short, 'description' => $description,
                            'seo_title' => mb_substr($name.' | SABA Equipamentos', 0, 255), 'seo_description' => $seo,
                            'availability' => 'unavailable', 'cost_price' => 0, 'price' => ($publishPreview && ! $fallback) ? $demoPrice : 0,
                            'promotional_price' => null, 'stock' => 0, 'weight' => null, 'height' => null,
                            'width' => null, 'length' => null, 'is_active' => ($publishPreview && ! $fallback), 'is_featured' => ($publishPreview && ! $fallback),
                            'whatsapp_quote_enabled' => true, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    } else {
                        $id = (int) $existing->id;
                        $updates = [];
                        if ($fallback) {
                            // Independente do estado anterior, Sem definicao nunca fica publicável.
                            $updates = ['category_id' => $category['id'], 'is_active' => false, 'availability' => 'unavailable'];
                        } elseif (! (bool) $existing->is_active) {
                            $updates['category_id'] = $category['id'];
                        }
                        if (! (bool) $existing->is_active && ! str_starts_with((string) $existing->sku, 'SABA-')
                            && ! DB::table('products')->where('sku', $sku)->where('id', '<>', $id)->exists()) {
                            $updates['sku'] = $sku;
                        }
                        if ($publishPreview && ! $fallback && ! (bool) $existing->is_active) {
                            $updates['is_active'] = true;
                            $updates['is_featured'] = true;
                            $updates['availability'] = 'unavailable';
                            if ((int) $existing->price === 0) $updates['price'] = $demoPrice;
                        }
                        if ($refreshText && ! (bool) $existing->is_active) {
                            $updates += ['name' => $name, 'short_description' => $short,
                                'description' => $description, 'seo_title' => mb_substr($name.' | SABA Equipamentos', 0, 255),
                                'seo_description' => $seo];
                        }
                        if ($updates !== []) {
                            DB::table('products')->where('id', $id)->update($updates + ['updated_at' => now()]);
                        }
                    }
                    // Só criar provenance depois de cadastrar o produto na mesma transação.
                    if (! $existingSource) {
                        $sourceId = DB::table('catalog_import_sources')->insertGetId([
                            'product_id' => $id, 'source_key' => $key, 'source_url' => $url,
                            'source_domain' => strtolower((string) parse_url($url, PHP_URL_HOST)),
                            'price_status' => (string) ($detail['price_status'] ?? ''),
                            'variant_details_status' => 'opcoes_nao_comprovam_combinacoes_vendaveis',
                            'metadata' => json_encode(['source_price_cents' => $detail['price'] ?? 0,
                                'collected_at' => now()->toIso8601String(), 'category_guess' => $categoryName], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    } else {
                        $sourceId = (int) $existingSource->id;
                    }
                    $savedOptions = 0;
                    foreach ($options as $position => $option) {
                        $savedOptions += DB::table('catalog_import_options')->insertOrIgnore([
                            'catalog_import_source_id' => $sourceId,
                            'name' => mb_substr($option['name'], 0, 120),
                            'value' => mb_substr($option['value'], 0, 255),
                            'status' => 'unverified', 'sort_order' => $position,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    // Pré-configuração dos 7 itens: apenas variantes RASCUNHO, nunca vendáveis.
                    $variantCount = 0;
                    if ($isNew || ! (bool) $existing->is_active) {
                        foreach ($draftVariants as $position => $attributes) {
                            $canonical = [];
                            foreach ($attributes as $attribute => $value) {
                                $canonical[mb_strtolower($attribute)] = mb_strtolower(trim($value));
                            }
                            ksort($canonical);
                            $combinationKey = hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                            $name = implode(' / ', array_keys($attributes));
                            $value = implode(' / ', array_values($attributes));
                            $variantCount += DB::table('product_variants')->insertOrIgnore([
                                'product_id' => $id, 'name' => $name, 'value' => $value,
                                'sku' => $sku.'-'.strtoupper(substr($combinationKey, 0, 10)),
                                'attributes' => json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                                'combination_key' => $combinationKey,
                                'price' => null, 'promotional_price' => null,
                                'stock' => 0, 'is_active' => false, 'sort_order' => $position,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                    }
                    return ['product_id' => $id, 'created' => $isNew, 'options' => $savedOptions,
                        'variants' => $variantCount, 'category_id' => $category['id'], 'category_created' => $category['created']];
                }, 3);
                $stats[$record['created'] ? 'created' : 'existing']++;
                if ($record['category_created']) $stats['categories_created']++;
                $stats['options'] += $record['options'];
                $stats['variant_drafts'] += $record['variants'];
                if ($fallback) {
                    $stats['uncategorized']++;
                } elseif ($publishPreview) {
                    $stats['preview_visible']++;
                }
                if (! $noImages) {
                    foreach ($imageUrls as $position => $imageUrl) {
                        try {
                            $path = $this->images->download($imageUrl, (string) parse_url($url, PHP_URL_HOST), $slug);
                            $linked = DB::transaction(function () use ($record, $path, $position, $name): bool {
                                $id = $record['product_id'];
                                DB::table('products')->where('id', $id)->lockForUpdate()->first();
                                if (DB::table('product_images')->where('product_id', $id)->where('path', $path)->exists()) {
                                    return false;
                                }
                                $primary = DB::table('product_images')->where('product_id', $id)->where('is_primary', true)->exists();
                                DB::table('product_images')->insert([
                                    'product_id' => $id, 'path' => $path, 'alt_text' => $name,
                                    'sort_order' => $position, 'is_primary' => ! $primary,
                                    'created_at' => now(), 'updated_at' => now(),
                                ]);
                                return true;
                            }, 3);
                            if ($linked) {
                                $stats['images']++;
                            }
                        } catch (Throwable $exception) {
                            $stats['image_errors']++;
                            $log('[AVISO IMAGEM] '.$imageUrl.' : '.$exception->getMessage());
                        }
                    }
                }
                $log(sprintf('[%s] Produto #%d, categoria #%d, SKU %s', $record['created'] ? 'CRIADO' : 'JÁ EXISTE',
                    $record['product_id'], $record['category_id'], $sku));
            } catch (Throwable $exception) {
                $stats['failed']++;
                $log('[ERRO] '.$url.' : '.$exception->getMessage());
            }
        }
        return $stats;
    }

    private function canonicalUrl(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! isset($parts['host'], $parts['path'])) {
            throw new InvalidArgumentException('URL de produto inválida: '.$url);
        }
        // Identidade independe de parâmetro de rastreamento de campanha.
        return strtolower($parts['host']).(rtrim($parts['path'], '/') ?: '/');
    }

    private function slugFromUrl(string $source): string
    {
        $slug = Str::slug(basename((string) parse_url('https://'.$source, PHP_URL_PATH)));
        if ($slug === '') {
            throw new InvalidArgumentException('URL sem identificador de produto.');
        }
        return mb_substr($slug, 0, 245);
    }

    private function presetFor(string $source): ?array
    {
        $presetHost = strtolower((string) parse_url((string) config('saba-homolog-inks.host'), PHP_URL_HOST));
        if ($presetHost !== strtolower((string) parse_url('https://'.$source, PHP_URL_HOST))) return null;
        foreach ((array) config('saba-homolog-inks.products', []) as $row) {
            if (str_ends_with($source, (string) $row['path'])) {
                return $row;
            }
        }
        return null;
    }

    private function normalized(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', strtolower(Str::ascii($value))) ?? '');
    }

    private function clean(string $value, int $max): string
    {
        $value = preg_replace('/tele[\s-]*silk/iu', 'SABA', $value) ?? $value;
        $value = preg_replace('/Dicas da Camilinha\s*:/iu', 'Orientações de uso:', $value) ?? $value;
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[ \t]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\n{3,}/u', "\n\n", $value) ?? $value;
        return mb_substr(trim($value), 0, $max);
    }

    private function classify(string $name, string $description): string
    {
        $name = $this->normalized($name);
        // Descrição pode mencionar diversas tintas: classificar prioritariamente pelo título.
        foreach ([$name] as $text) {
            if (preg_match('/plastisol/', $text)) return 'Tinta Plastisol';
            if (preg_match('/hidrocryl|base (?:d.?|de )?agua|base aquosa|tinta de agua/', $text)) return 'Tinta Base Água';
            if (preg_match('/\bberco\b|tabua de impressao/', $text)) return 'Berços';
            if (preg_match('/carrossel|maquina de serigrafia|mesa serigrafica/', $text)) return 'Máquinas de Serigrafia';
            if (preg_match('/\brodo\b|rodos/', $text)) return 'Rodos';
            if (preg_match('/\btela\b|\bquadro\b|quadros/', $text)) return 'Telas e Quadros';
            if (preg_match('/estufa|flash cure|secagem|cura termica/', $text)) return 'Secagem e Cura';
            if (preg_match('/acessorio|morcete|chaveta|\bmola\b|cola permanente/', $text)) return 'Acessórios';
        }
        return 'Sem definicao';
    }

    private function skuPrefix(string $category): string
    {
        return match ($this->normalized($category)) {
            'tinta base agua' => 'TA', 'tinta plastisol' => 'TP', 'bercos' => 'BE',
            'maquinas de serigrafia' => 'MA', 'acessorios' => 'AC', 'rodos' => 'RO',
            'telas e quadros' => 'TQ', 'secagem e cura' => 'SC', default => 'SD',
        };
    }

    /** @return array{id:int,created:bool} */
    private function category(string $name, bool $fallback): array
    {
        $name = $fallback ? 'Sem definicao' : $this->clean($name, 255);
        $slug = $fallback ? 'sem-definicao' : Str::slug($name);
        if ($slug === '') {
            throw new InvalidArgumentException('Nome de categoria inválido.');
        }
        $item = DB::table('categories')->where('slug', $slug)->lockForUpdate()->first();
        if (! $item) {
            $item = DB::table('categories')->get(['id', 'name', 'slug'])->first(fn (object $row) => $this->normalized($row->name) === $this->normalized($name));
        }
        if ($item) {
            if ($fallback) {
                DB::table('categories')->where('id', $item->id)->update([
                    'name' => 'Sem definicao', 'slug' => 'sem-definicao',
                    'is_active' => false, 'updated_at' => now(),
                ]);
            }
            return ['id' => (int) $item->id, 'created' => false];
        }
        $id = DB::table('categories')->insertGetId([
            'name' => $name, 'slug' => $slug, 'description' => null,
            'sort_order' => 0, 'is_active' => ! $fallback,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return ['id' => $id, 'created' => true];
    }

    /** @param ?array<string,mixed> $preset @param list<array{name:string,value:string}> $found
     *  @return list<array{name:string,value:string}> */
    private function options(?array $preset, array $found): array
    {
        $rows = [];
        foreach ($preset ? [] : $found as $foundRow) {
            $name = $this->clean((string) ($foundRow['name'] ?? ''), 120);
            $value = $this->clean((string) ($foundRow['value'] ?? ''), 255);
            if ($name !== '' && $value !== '') $rows[$name.'|'.$value] = compact('name', 'value');
        }
        if ($preset) {
            foreach ((array) ($preset['colors'] ?? []) as $value) {
                $name = 'Cor';
                $value = $this->clean((string) $value, 255);
                $rows[$name.'|'.$value] = compact('name', 'value');
            }
            foreach ((array) ($preset['packages'] ?? []) as $value) {
                $name = 'Embalagem';
                $value = $this->clean((string) $value, 255);
                $rows[$name.'|'.$value] = compact('name', 'value');
            }
        }
        return array_slice(array_values($rows), 0, 200);
    }

    /** @param ?array<string,mixed> $preset
     *  @return list<array<string,string>> */
    private function draftVariants(?array $preset): array
    {
        // Somente os sete presets recebem pré-combinações de rascunho; outros produtos
        // guardam opções individualmente para evitar inventar combinações/SKUs vendáveis.
        if (! $preset || ($preset['colors'] ?? []) === []) return [];
        $combinations = [];
        $packages = $preset['packages'] ?: [''];
        foreach ($preset['colors'] as $color) {
            foreach ($packages as $package) {
                $row = ['Cor' => $this->clean((string) $color, 255)];
                if ($package !== '') $row['Embalagem'] = $this->clean((string) $package, 255);
                $combinations[] = $row;
            }
        }
        return array_slice($combinations, 0, 100);
    }
}
