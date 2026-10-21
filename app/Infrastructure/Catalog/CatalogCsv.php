<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use InvalidArgumentException;
use RuntimeException;

final class CatalogCsv
{
    /** @return list<array<string, string>> */
    public function read(string $filename): array
    {
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new InvalidArgumentException("CSV não encontrado: {$filename}");
        }

        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Não foi possível abrir: {$filename}");
        }

        try {
            $line = fgets($handle);
            if ($line === false) {
                return [];
            }
            $delimiter = substr_count($line, ';') >= substr_count($line, ',') ? ';' : ',';
            rewind($handle);
            $headers = fgetcsv($handle, 0, $delimiter, '"', '');
            if (! is_array($headers) || $headers === []) {
                throw new InvalidArgumentException("Cabeçalho CSV inválido: {$filename}");
            }
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]) ?? $headers[0];
            $headers = array_map(static fn ($name): string => trim((string) $name), $headers);
            if (count(array_unique($headers)) !== count($headers) || in_array('', $headers, true)) {
                throw new InvalidArgumentException("Cabeçalho CSV contém colunas vazias ou repetidas: {$filename}");
            }

            $rows = [];
            while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
                if ($data === [null] || ! array_filter($data, static fn ($cell): bool => trim((string) $cell) !== '')) {
                    continue;
                }
                if (count($data) !== count($headers)) {
                    throw new InvalidArgumentException('Número de colunas inválido na linha '.(count($rows) + 2)." de {$filename}");
                }
                $rows[] = array_combine($headers, array_map(static fn ($value): string => trim((string) $value), $data));
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /** @param list<array<string, scalar|null>> $rows */
    public function write(string $filename, array $rows, array $columns): void
    {
        $directory = dirname($filename);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Não foi possível criar {$directory}");
        }
        $handle = fopen($filename, 'wb');
        if ($handle === false) {
            throw new RuntimeException("Não foi possível gravar {$filename}");
        }

        try {
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns, ';', '"', '');
            foreach ($rows as $row) {
                fputcsv($handle, array_map(static fn ($column): string => (string) ($row[$column] ?? ''), $columns), ';', '"', '');
            }
        } finally {
            fclose($handle);
        }
    }
}
