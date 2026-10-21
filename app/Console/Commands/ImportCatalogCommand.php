<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Catalog\ImportCatalogCsvAction;
use Illuminate\Console\Command;

final class ImportCatalogCommand extends Command
{
    protected $signature = 'saba:catalog:import
        {file : Caminho do CSV de produtos}
        {--images= : Caminho do CSV de imagens}
        {--options= : Caminho do CSV de opções}
        {--dry-run : Confere os registros sem alterar o banco nem baixar imagens}
        {--no-images : Importa sem baixar imagens}
        {--update-text : Atualiza somente textos de produtos previamente importados e inativos}';

    protected $description = 'Importa rascunhos de produtos, vínculos de categorias e imagens locais; mantém opções não confirmadas em staging.';

    public function handle(ImportCatalogCsvAction $action): int
    {
        $file = $this->absolutePath((string) $this->argument('file'));
        $images = $this->option('images') ? $this->absolutePath((string) $this->option('images')) : null;
        $options = $this->option('options') ? $this->absolutePath((string) $this->option('options')) : null;

        try {
            $result = $action->execute($file, [
                'images' => $images,
                'options' => $options,
                'dry_run' => (bool) $this->option('dry-run'),
                'no_images' => (bool) $this->option('no-images'),
                'update_text' => (bool) $this->option('update-text'),
            ], fn (string $message) => $this->line($message));
            $this->table(['Métrica', 'Quantidade'], array_map(
                static fn ($key, $value): array => [$key, $value],
                array_keys($result), array_values($result)
            ));
            return $result['failed'] > 0 || $result['images_failed'] > 0 ? self::FAILURE : self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }

    private function absolutePath(string $filename): string
    {
        return str_starts_with($filename, '/') ? $filename : base_path($filename);
    }
}
