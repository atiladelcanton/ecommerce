<?php

declare(strict_types=1);

namespace App\Support\Catalog;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class ProductDescription
{
    public static function html(?string $description): string
    {
        if ($description === null || $description === '') {
            return '';
        }

        // Preserve legacy plain-text descriptions, including their line breaks.
        if (! preg_match('/<\/?[a-z][^>]*>/i', $description)) {
            return nl2br(e($description), false);
        }

        $config = (new HtmlSanitizerConfig)
            ->allowElement('a', ['href', 'title'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks();

        foreach (['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote'] as $tag) {
            $config = $config->allowElement($tag);
        }

        foreach (['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'] as $tag) {
            $config = $config->dropElement($tag);
        }

        return (new HtmlSanitizer($config))->sanitize($description);
    }
}
