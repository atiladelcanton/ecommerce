<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Catalog\CrawlCatalogSiteAction;
use Illuminate\Console\Command;

final class CrawlCatalogCommand extends Command
{
    protected $signature = 'saba:catalog:crawl
        {url : URL pública da página de categoria ou de um produto}
        {--category= : Nome ou slug da categoria já cadastrada no SABA}
        {--map= : Caminho de mapa JSON para site com várias categorias}
        {--max-pages=10 : Limite de páginas da categoria}
        {--max-products=100 : Limite de produtos coletados}
        {--delay-ms=800 : Intervalo entre requisições em milissegundos (mínimo 400)}
        {--product-pattern= : Regex PHP opcional para identificar URLs de produto em outros sites}
        {--no-import : Apenas gera os CSVs, sem cadastrar no banco}
        {--dry-run : Gera CSVs e valida vinculação sem salvar no banco}
        {--no-images : Não baixa imagens, mas preserva URLs nos CSVs}';

    protected $description = 'Coleta páginas públicas, gera CSV e importa produtos inativos, fotos locais e opções de conferência.';

    public function handle(CrawlCatalogSiteAction $action): int
    {
        try {
            $map = $this->option('map');
            $result = $action->execute((string) $this->argument('url'), [
                'category' => (string) ($this->option('category') ?? ''),
                'map' => $map ? (str_starts_with((string) $map, '/') ? $map : base_path((string) $map)) : null,
                'max_pages' => (int) $this->option('max-pages'),
                'max_products' => (int) $this->option('max-products'),
                'delay_ms' => (int) $this->option('delay-ms'),
                'product_pattern' => $this->option('product-pattern') ?: null,
                'import' => ! $this->option('no-import'),
                'dry_run' => (bool) $this->option('dry-run'),
                'no_images' => (bool) $this->option('no-images'),
            ], fn (string $message) => $this->line($message));
            $this->info(sprintf('Coleta: %d produtos, %d fotos, %d opções. Pasta: %s', $result['products'], $result['images'], $result['options'], $result['directory']));
            if ($result['statistics'] !== null) {
                $this->table(['Métrica', 'Quantidade'], array_map(
                    static fn ($key, $value): array => [$key, $value],
                    array_keys($result['statistics']), array_values($result['statistics'])
                ));
            }
            return $result['products'] === 0 || ($result['statistics']['failed'] ?? 0) > 0
                || ($result['statistics']['images_failed'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
