<?php

declare(strict_types=1);

namespace App\Support\Storefront;

final class Links
{
    public static function whatsapp(?string $phone, ?string $message = null): ?string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');
        if (! preg_match('/^[1-9][0-9]{9,14}$/D', $digits)) {
            return null;
        }

        return 'https://wa.me/'.$digits.($message ? '?text='.rawurlencode($message) : '');
    }

    public static function youtube(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $parts = parse_url($url);
        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $id = null;
        if ($host === 'youtu.be') {
            $id = $path;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^(?:embed|shorts)/([a-zA-Z0-9_-]{11})$~D', $path, $matches)) {
                $id = $matches[1];
            }
        }

        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/D', $id) ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
    }
}
