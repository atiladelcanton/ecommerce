<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Catalog\ImportCatalogUrlsAction;
use Illuminate\Console\Command;
use Throwable;

final class ImportCatalogUrlsCommand extends Command
{
    protected $signature = 'saba:catalog:urls
        {urls?* : Uma ou várias URLs DIRETAS de produtos}
        {--preset= : Use --preset=tintas para importar os 7 produtos iniciais}
        {--file= : Caminho de arquivo TXT, com uma URL por linha}
        {--category= : Força categoria para todas as URLs, criando-a se necessário}
        {--dry-run : Apenas consulta e valida as páginas; não grava no banco nem baixa imagens}
        {--no-images : Não baixar nem vincular imagens}
        {--refresh-text : Atualiza títulos e descrições somente dos produtos ainda inativos}
        {--publish-preview : Exibe rascunhos na vitrine SOMENTE em homolog, sem permitir compra}
        {--demo-price-cents= : Preço ilustrativo obrigatório em centavos (ex.: 9990) com --publish-preview}';

    protected $description = 'Importa URLs individuais de produtos como rascunhos SABA, cria categorias e baixa imagens locais.';

    public function handle(ImportCatalogUrlsAction $action): int
    {
        $urls = array_values((array) $this->argument('urls'));
        $file = $this->option('file');
        if (is_string($file) && $file !== '') {
            $path = str_starts_with($file, '/') ? $file : base_path($file);
            if (! is_file($path) || ! is_readable($path)) {
                $this->error('Arquivo de URLs inexistente ou sem permissão de leitura: '.$path);
                return self::FAILURE;
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                $this->error('Falha ao ler arquivo: '.$path);
                return self::FAILURE;
            }
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && ! str_starts_with($line, '#')) $urls[] = $line;
            }
        }
        $preset = trim((string) ($this->option('preset') ?? ''));
        if ($preset !== '') {
            if ($preset !== 'tintas') {
                $this->error('Preset não reconhecido. Use --preset=tintas.');
                return self::FAILURE;
            }
            foreach ((array) config('saba-homolog-inks.products', []) as $product) {
                $urls[] = rtrim((string) config('saba-homolog-inks.host'), '/').$product['path'];
            }
        }
        if ($urls === []) {
            $this->error('Informe URLs, --file=import-data/urls.txt ou --preset=tintas.');
            return self::FAILURE;
        }
        try {
            $demo = $this->option('demo-price-cents');
            if ($demo !== null && (! is_string($demo) || ! preg_match('/^[1-9][0-9]{0,10}$/', $demo))) {
                $this->error('--demo-price-cents exige inteiro positivo em centavos.');
                return self::FAILURE;
            }
            if ($this->option('publish-preview')) {
                $this->warn('Homologação: produtos visíveis com preço ILUSTRATIVO e compra desativada.');
                if (! $this->confirm('Confirma a publicação demonstrativa?', false)) return self::FAILURE;
            }
            $stats = $action->execute($urls, [
                'category' => $this->option('category'),
                'dry_run' => (bool) $this->option('dry-run'),
                'no_images' => (bool) $this->option('no-images'),
                'refresh_text' => (bool) $this->option('refresh-text'),
                'publish_preview' => (bool) $this->option('publish-preview'),
                'demo_price_cents' => $demo !== null ? (int) $demo : null,
            ], fn (string $text) => $this->line($text));
            $this->table(['Métrica', 'Total'], array_map(
                static fn ($key, $number): array => [$key, $number],
                array_keys($stats), array_values($stats)
            ));
            return $stats['failed'] === 0 && $stats['image_errors'] === 0 ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
