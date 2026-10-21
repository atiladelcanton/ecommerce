<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Infrastructure\Catalog\CatalogCsv;
use App\Infrastructure\Catalog\CatalogHtmlExtractor;
use App\Infrastructure\Catalog\SafeCatalogHttp;
use RuntimeException;

final class CrawlCatalogSiteAction
{
    public function __construct(
        private readonly SafeCatalogHttp $http,
        private readonly CatalogHtmlExtractor $extractor,
        private readonly CatalogCsv $csv,
        private readonly ImportCatalogCsvAction $importer,
    ) {}

    /**
     * @param array{category?:string,map?:?string,max_pages?:int,max_products?:int,delay_ms?:int,product_pattern?:?string,import?:bool,dry_run?:bool,no_images?:bool} $arguments
     * @param callable(string):void $log
     * @return array{directory:string,products:int,images:int,options:int,statistics:array<string,int>|null}
     */
    public function execute(string $url, array $arguments, callable $log): array
    {
        $this->http->assertPublicUrl($url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $origin = strtolower((string) parse_url($url, PHP_URL_SCHEME)).'://'.$host;
        $maxPages = max(1, min(50, (int) ($arguments['max_pages'] ?? 10)));
        $maxProducts = max(1, min(1000, (int) ($arguments['max_products'] ?? 100)));
        $delayMs = max(400, min(5000, (int) ($arguments['delay_ms'] ?? 800)));
        $productPattern = $arguments['product_pattern'] ?? null;
        if ($productPattern !== null && @preg_match($productPattern, '/teste') === false) {
            throw new RuntimeException('--product-pattern deve conter expressão regular PHP com delimitadores válida.');
        }
        $category = trim((string) ($arguments['category'] ?? ''));
        $map = $this->readMap($arguments['map'] ?? null);
        $this->checkRobots($origin, $url);
        $rootPath = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
        $isRoot = $rootPath === '' || $rootPath === '/';
        $sitePages = [];

        if ($isRoot && $map !== []) {
            foreach ($map['sources'] as $source) {
                $sourceUrl = $this->extractor->absoluteUrl($source['path'] ?? '', $origin);
                if ($sourceUrl !== null && $this->sameHost($sourceUrl, $host)) {
                    $sitePages[] = ['url' => $sourceUrl, 'mapping' => $source];
                }
            }
        } else {
            $mapping = $this->matchMapping($url, $map);
            if ($category !== '') {
                $mapping['category'] = $category;
            }
            if (($mapping['category'] ?? '') === '') {
                throw new RuntimeException('Informe --category=slug-local ou --map=arquivo.json para associar as categorias corretamente.');
            }
            $sitePages[] = ['url' => $url, 'mapping' => $mapping];
        }
        if ($sitePages === []) {
            throw new RuntimeException('Nenhuma categoria configurada para a URL. Informe --category ou revise --map.');
        }
        if ($isRoot && $map === [] && $category !== '') {
            throw new RuntimeException('Para a home do site, use um mapa de categorias. Um único slug não é seguro para todo o catálogo.');
        }

        $products = [];
        $images = [];
        $options = [];
        $seen = [];
        foreach ($sitePages as $seed) {
            if (count($products) >= $maxProducts) {
                break;
            }
            $listingUrl = $seed['url'];
            $mapping = $seed['mapping'];
            $listingPath = rtrim((string) parse_url($listingUrl, PHP_URL_PATH), '/');
            $queue = [$listingUrl];
            $visited = [];
            $productUrls = [];
            if ($this->isProductUrl($listingUrl, $productPattern)) {
                $productUrls[] = $listingUrl;
            }
            while ($queue !== [] && count($visited) < $maxPages && ! $this->isProductUrl($listingUrl, $productPattern)) {
                $page = array_shift($queue);
                if (isset($visited[$page])) {
                    continue;
                }
                $visited[$page] = true;
                $this->checkRobots($origin, $page);
                $log('[CATEGORIA] '.$page);
                $html = $this->http->get($page);
                $this->pause($delayMs);
                foreach ($this->extractor->links($html, $page) as $link) {
                    if (! $this->sameHost($link, $host)) {
                        continue;
                    }
                    if ($this->isProductUrl($link, $productPattern)) {
                        $productUrls[$link] = $link;
                    } else {
                        // Somente páginas paginadas deste caminho de categoria; não visitar o menu global.
                        $path = rtrim((string) parse_url($link, PHP_URL_PATH), '/');
                        $query = (string) parse_url($link, PHP_URL_QUERY);
                        if ($path === $listingPath && preg_match('/(?:^|&)page=\d+(?:&|$)/', $query)
                            && ! isset($visited[$link]) && ! in_array($link, $queue, true)) {
                            $queue[] = $link;
                        }
                    }
                }
            }
            foreach (array_values($productUrls) as $productUrl) {
                if (count($products) >= $maxProducts) {
                    break;
                }
                $uniqueKey = strtolower($productUrl);
                if (isset($seen[$uniqueKey])) {
                    continue;
                }
                $seen[$uniqueKey] = true;
                try {
                    $this->checkRobots($origin, $productUrl);
                    $html = $this->http->get($productUrl);
                    $this->pause($delayMs);
                    $item = $this->extractor->product($html, $productUrl);
                    if ($item['name'] === '') {
                        $log('[IGNORADO: sem nome] '.$productUrl);
                        continue;
                    }
                    $slug = $this->slug($productUrl);
                    $categoryName = $this->classifyCategory($item['name'], $mapping);
                    if ($categoryName === '') {
                        $log('[IGNORADO: sem categoria] '.$productUrl);
                        continue;
                    }
                    $sourceOptions = [];
                    foreach ($item['options'] as $option) {
                        $sourceOptions[$option['name']][] = $option['value'];
                        $options[] = [
                            'product_slug' => $slug, 'source_options_count' => count($item['options']),
                            'option_name' => $option['name'], 'option_value' => $option['value'],
                            'price' => '', 'promotional_price' => '', 'stock' => '',
                            'status' => 'opcao_visivel_nao_eh_variante_sku_confirmada',
                            'source_url' => $productUrl,
                        ];
                    }
                    foreach ($item['images'] as $number => $imageUrl) {
                        $images[] = [
                            'product_slug' => $slug, 'image_url' => $imageUrl,
                            'alt_text' => $item['name'], 'sort_order' => $number,
                            'is_primary' => $number === 0 ? 1 : 0, 'source_url' => $productUrl,
                        ];
                    }
                    $products[] = [
                        'id' => '', 'category_id' => '', 'category_name' => $categoryName,
                        'name' => $item['name'], 'slug' => $slug, 'sku' => $item['sku'],
                        'short_description' => mb_substr($item['description'], 0, 400),
                        'description' => $item['description'],
                        'seo_title' => $item['seo_title'] ?: $item['name'],
                        'seo_description' => $item['seo_description'] ?: mb_substr($item['description'], 0, 400),
                        'availability' => 'unavailable', 'cost_price' => '0',
                        'price' => $item['price'], 'promotional_price' => '', 'stock' => '0',
                        'weight' => '', 'height' => '', 'width' => '', 'length' => '',
                        'is_active' => '0', 'is_featured' => '0', 'whatsapp_quote_enabled' => '1',
                        'image_url' => $item['images'][0] ?? '', 'source_url' => $productUrl,
                        'source_category_url' => $listingUrl,
                        'source_product_id' => $this->sourceProductId($slug),
                        'source_options_count' => count($item['options']),
                        'source_options_json' => json_encode($sourceOptions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'source_min_price_brl' => '', 'source_regular_price_brl' => '',
                        'price_status' => $item['price_status'],
                        'image_status' => $item['images'] === [] ? 'imagem_nao_exibida' : 'url_obtida',
                        'variant_details_status' => $item['options'] === [] ? 'opcoes_nao_recuperadas' : 'opcoes_visiveis_sem_combinacoes_ou_precos',
                        'crawler_date' => now()->toDateString(),
                    ];
                    $log('[PRODUTO] '.$item['name'].' -> '.$categoryName.' / '.$slug);
                } catch (\Throwable $exception) {
                    $log('[FALHA] '.$productUrl.': '.$exception->getMessage());
                    if (str_contains($exception->getMessage(), '(HTTP 403)') || str_contains($exception->getMessage(), '(HTTP 429)')) {
                        throw $exception;
                    }
                }
            }
        }

        $folder = storage_path('app/catalog-imports/crawl-'.now()->format('Ymd-His').'-'.substr(hash('sha256', $url), 0, 8));
        $productFile = $folder.'/products.csv';
        $imageFile = $folder.'/images.csv';
        $optionFile = $folder.'/options.csv';
        $this->csv->write($productFile, $products, [
            'id', 'category_id', 'category_name', 'name', 'slug', 'sku', 'short_description', 'description',
            'seo_title', 'seo_description', 'availability', 'cost_price', 'price', 'promotional_price',
            'stock', 'weight', 'height', 'width', 'length', 'is_active', 'is_featured', 'whatsapp_quote_enabled',
            'image_url', 'source_url', 'source_category_url', 'source_product_id', 'source_options_count',
            'source_options_json', 'source_min_price_brl', 'source_regular_price_brl', 'price_status',
            'image_status', 'variant_details_status', 'crawler_date',
        ]);
        $this->csv->write($imageFile, $images, ['product_slug', 'image_url', 'alt_text', 'sort_order', 'is_primary', 'source_url']);
        $this->csv->write($optionFile, $options, [
            'product_slug', 'source_options_count', 'option_name', 'option_value',
            'price', 'promotional_price', 'stock', 'status', 'source_url',
        ]);
        $stats = null;
        $log('[CSV] '.$folder);
        if (($arguments['import'] ?? true) && $products !== []) {
            $stats = $this->importer->execute($productFile, [
                'images' => $imageFile, 'options' => $optionFile,
                'dry_run' => (bool) ($arguments['dry_run'] ?? false),
                'no_images' => (bool) ($arguments['no_images'] ?? false),
            ], $log);
        }
        return ['directory' => $folder, 'products' => count($products), 'images' => count($images), 'options' => count($options), 'statistics' => $stats];
    }

    private function pause(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    private function isProductUrl(string $url, ?string $pattern = null): bool
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        return preg_match($pattern ?? '~/(produto|product|products|item|p)/[^/?#]+~i', $path) === 1;
    }

    private function sameHost(string $url, string $host): bool
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST)) === $host;
    }

    private function slug(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $name = basename($path);
        $slug = \Illuminate\Support\Str::slug(rawurldecode($name));
        return mb_substr($slug, 0, 245);
    }

    private function sourceProductId(string $slug): string
    {
        return preg_match('/-(\d+)$/', $slug, $match) ? $match[1] : '';
    }

    /** @return array<string,mixed> */
    private function readMap(?string $filename): array
    {
        if ($filename === null || $filename === '') {
            return [];
        }
        if (! is_file($filename)) {
            throw new RuntimeException('Mapa de categorias não encontrado: '.$filename);
        }
        $map = json_decode((string) file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($map) || ! isset($map['sources']) || ! is_array($map['sources'])) {
            throw new RuntimeException('Formato inválido de mapa: requer objeto {"sources": [...]}');
        }
        return $map;
    }

    private function matchMapping(string $url, array $map): array
    {
        foreach (($map['sources'] ?? []) as $source) {
            $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
            $sourcePath = rtrim((string) ($source['path'] ?? ''), '/');
            if ($sourcePath !== '' && $path === $sourcePath) {
                return $source;
            }
        }
        return [];
    }

    private function classifyCategory(string $productName, array $mapping): string
    {
        foreach (($mapping['rules'] ?? []) as $rule) {
            $needle = $rule['match'] ?? '';
            if (is_string($needle) && $needle !== '' && mb_stripos($productName, $needle) !== false) {
                return (string) ($rule['category'] ?? '');
            }
        }
        return (string) ($mapping['category'] ?? '');
    }

    private function checkRobots(string $origin, string $url): void
    {
        static $memo = [];
        if (! array_key_exists($origin, $memo)) {
            try {
                $memo[$origin] = $this->http->get($origin.'/robots.txt', 131072);
            } catch (\Throwable $exception) {
                if (! str_contains($exception->getMessage(), 'HTTP 404')) {
                    throw new RuntimeException('Não foi possível confirmar robots.txt: '.$exception->getMessage(), 0, $exception);
                }
                $memo[$origin] = '';
            }
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        $robots = (string) $memo[$origin];
        $applies = false;
        $rules = [];
        foreach (preg_split('/\r?\n/', $robots) as $line) {
            $line = trim(explode('#', $line, 2)[0]);
            if (preg_match('/^user-agent:\s*(.*)$/i', $line, $match)) {
                $applies = trim($match[1]) === '*' || stripos(trim($match[1]), 'SabaCatalogImport') !== false;
            } elseif ($applies && preg_match('/^disallow:\s*(.*)$/i', $line, $match)) {
                $rule = trim($match[1]);
                if ($rule !== '') {
                    $rules[] = $rule;
                }
            }
        }
        foreach ($rules as $rule) {
            $prefix = explode('*', $rule, 2)[0];
            if ($prefix !== '' && str_starts_with($path, rtrim($prefix, '$'))) {
                throw new RuntimeException('robots.txt não permite coleta de '.$path);
            }
        }
    }
}
