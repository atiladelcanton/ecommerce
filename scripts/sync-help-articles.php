<?php

declare(strict_types=1);

/**
 * Synchronizes the Markdown guides with the PHP fallback used by Admin > Ajuda.
 * Usage: php scripts/sync-help-articles.php [--check]
 * Keep this in the same change whenever updating an operator-facing feature.
 */

$root = dirname(__DIR__);
$source = $root.'/app/Filament/Support/BundledHelpArticles.php';
$topics = array_keys((require $root.'/config/admin_help.php')['topics']);
$current = file_get_contents($source);
if (! is_string($current)) {
    fwrite(STDERR, "Could not read the bundled help articles.\n");
    exit(1);
}

$updated = $current;
foreach ($topics as $slug) {
    $articlePath = $root.'/resources/help/'.$slug.'.md';
    $article = is_file($articlePath) ? file_get_contents($articlePath) : false;
    if (! is_string($article) || trim($article) === '') {
        fwrite(STDERR, "Missing or empty help article: $articlePath\n");
        exit(1);
    }

    $delimiter = 'ARTICLE_'.strtoupper(str_replace('-', '_', $slug)).'_END';
    if (str_contains($article, $delimiter)) {
        fwrite(STDERR, "Help article uses a reserved delimiter: $slug\n");
        exit(1);
    }
    // Keep the content exactly equal to the Markdown file (minus trailing newlines).
    $replacement = "'{$slug}' => <<<'{$delimiter}'\n".rtrim($article, "\r\n")."\n{$delimiter},";
    $pattern = "/'".preg_quote($slug, '/')."'\\s*=>\\s*<<<'".preg_quote($delimiter, '/')."'\\R.*?\\R".preg_quote($delimiter, '/').",/s";
    $count = 0;
    $next = preg_replace_callback($pattern, static fn (): string => $replacement, $updated, 1, $count);
    if (! is_string($next) || $count !== 1) {
        fwrite(STDERR, "Could not find article in bundle: $slug\n");
        exit(1);
    }
    $updated = $next;
}

if (in_array('--check', $argv, true)) {
    if ($updated !== $current) {
        fwrite(STDERR, "Bundled help articles are out of date. Run: php scripts/sync-help-articles.php\n");
        exit(1);
    }
    echo "All ".count($topics)." help articles are synchronized.\n";
    exit(0);
}

if ($updated !== $current && file_put_contents($source, $updated) === false) {
    fwrite(STDERR, "Could not update the bundled help articles.\n");
    exit(1);
}

echo "Synchronized ".count($topics)." help articles.\n";
