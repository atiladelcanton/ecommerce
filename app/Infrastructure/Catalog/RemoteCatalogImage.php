<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class RemoteCatalogImage
{
    public function __construct(private readonly SafeCatalogHttp $http) {}

    public function download(string $url, string $sourceDomain, string $productSlug): string
    {
        if ($url === '') {
            throw new RuntimeException('Imagem sem URL');
        }
        $disk = Storage::disk('public');
        $hash = hash('sha256', $url);
        $prefix = 'products/imported/'.substr(hash('sha256', $sourceDomain), 0, 12);
        $basename = substr($productSlug, 0, 72).'-'.substr($hash, 0, 18);

        // Reexecuções não baixam outra vez a mesma imagem. Um caminho válido sempre
        // é relativo ao disco public; nunca armazena URL no product_images.path.
        foreach (['jpg', 'png', 'webp'] as $extension) {
            $candidate = "{$prefix}/{$basename}.{$extension}";
            if ($disk->exists($candidate)) {
                return $candidate;
            }
        }

        $urls = [$url];
        if (str_contains($url, 'cdn.vnda.com.br/150x/')) {
            array_unshift($urls, str_replace('cdn.vnda.com.br/150x/', 'cdn.vnda.com.br/1200x/', $url));
        }
        $lastError = null;
        foreach (array_unique($urls) as $candidateUrl) {
            try {
                $binary = $this->http->get($candidateUrl, 5 * 1024 * 1024);
                $info = @getimagesizefromstring($binary);
                if (! is_array($info) || ! isset($info['mime'])) {
                    throw new RuntimeException('URL não retornou uma imagem válida');
                }
                $extension = match ($info['mime']) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => throw new RuntimeException('Formato de imagem não permitido: '.$info['mime']),
                };
                $path = "{$prefix}/{$basename}.{$extension}";
                if (! $disk->put($path, $binary)) {
                    throw new RuntimeException('Falha ao salvar a imagem no disco público');
                }
                return $path;
            } catch (\Throwable $exception) {
                $lastError = $exception;
            }
        }
        throw new RuntimeException('Falha ao baixar imagem: '.($lastError?->getMessage() ?? $url));
    }
}
