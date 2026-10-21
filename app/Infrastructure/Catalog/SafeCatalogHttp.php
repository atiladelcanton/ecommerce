<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Consultas públicas de leitura para o crawler. Não segue redirects, não usa credenciais,
 * bloqueia DNS/IPs privados e limita o tamanho recebido. Recomenda-se defesa de saída de rede.
 */
final class SafeCatalogHttp
{
    public function get(string $url, int $maxBytes = 2097152): string
    {
        $this->assertPublicUrl($url);

        $response = Http::withHeaders([
            'User-Agent' => 'SabaCatalogImport/1.0 (+contato com administrador da loja)',
            'Accept' => 'text/html,application/ld+json,application/xml,image/avif,image/webp,image/png,image/jpeg,*/*;q=0.8',
        ])->withOptions([
            'allow_redirects' => false,
            'stream' => true,
            'force_ip_resolve' => 'v4',
        ])->connectTimeout(5)->timeout(25)->get($url);

        if ($response->status() === 429 || $response->status() === 403) {
            throw new RuntimeException('Site bloqueou/limitou a coleta (HTTP '.$response->status().'): '.$url);
        }
        if ($response->status() >= 300 && $response->status() < 400) {
            throw new RuntimeException('Redirecionamento recusado por segurança: '.$url);
        }
        if (! $response->successful()) {
            throw new RuntimeException('Falha HTTP '.$response->status().': '.$url);
        }
        $contentLength = (int) $response->header('Content-Length');
        if ($contentLength > $maxBytes) {
            throw new RuntimeException('Resposta excede o limite permitido: '.$url);
        }

        $stream = $response->toPsrResponse()->getBody();
        $bytes = '';
        while (! $stream->eof()) {
            $chunk = $stream->read(min(32768, $maxBytes + 1 - strlen($bytes)));
            if ($chunk === '') {
                break;
            }
            $bytes .= $chunk;
            if (strlen($bytes) > $maxBytes) {
                throw new RuntimeException('Resposta excede o limite permitido: '.$url);
            }
        }

        return $bytes;
    }

    public function assertPublicUrl(string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['host'], $parts['scheme'])) {
            throw new RuntimeException('URL inválida: '.$url);
        }
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        if (! in_array($scheme, ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || (isset($parts['port']) && ! in_array($parts['port'], [80, 443], true))
            || $host === 'localhost' || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.local') || str_ends_with($host, '.internal')
            || str_contains($host, '%')) {
            throw new RuntimeException('URL/host não permitido: '.$url);
        }

        // IPv6 literal não é aceito; impede bypass através de ::1 e IPv4 mapeado.
        if (str_contains($host, ':')) {
            throw new RuntimeException('Host IPv6 literal não permitido: '.$url);
        }
        $ips = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? [$host] : gethostbynamel($host);
        if (! is_array($ips) || $ips === []) {
            throw new RuntimeException('DNS sem endereço IPv4 válido: '.$host);
        }
        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                || $this->isRestrictedIpv4($ip)) {
                throw new RuntimeException('Endereço IP de rede interna/restrita não permitido: '.$host);
            }
        }
    }

    private function isRestrictedIpv4(string $ip): bool
    {
        $unsigned = (int) sprintf('%u', ip2long($ip));
        foreach ([
            [0x00000000, 0xFF000000], // 0/8
            [0x64400000, 0xFFC00000], // 100.64/10
            [0xA9FE0000, 0xFFFF0000], // 169.254/16
            [0xC0000000, 0xFFFFFF00], // 192.0.0/24
            [0xC6120000, 0xFFFE0000], // 198.18/15
            [0xC0000200, 0xFFFFFF00], // 192.0.2/24
            [0xC6336400, 0xFFFFFF00], // 198.51.100/24
            [0xCB007100, 0xFFFFFF00], // 203.0.113/24
            [0xE0000000, 0xF0000000], // multicast
            [0xF0000000, 0xF0000000], // reserved
        ] as [$network, $mask]) {
            if (($unsigned & $mask) === $network) {
                return true;
            }
        }
        return false;
    }
}
