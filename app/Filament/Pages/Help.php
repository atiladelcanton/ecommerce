<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Support\BundledHelpArticles;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/** Read-only handbook for authenticated back-office users. */
final class Help extends Page
{
    protected static ?string $title = 'Ajuda';

    protected static ?string $navigationLabel = 'Ajuda e tutoriais';

    protected static ?string $slug = 'ajuda';

    protected static string|\UnitEnum|null $navigationGroup = 'Ajuda';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.help';

    public function getViewData(): array
    {
        $topics = (array) config('admin_help.topics', []);
        $rawTopic = request()->query('topico', 'primeiros-passos');
        $requested = is_string($rawTopic) ? $rawTopic : 'primeiros-passos';
        $selected = array_key_exists($requested, $topics) ? $requested : 'primeiros-passos';
        $rawTerm = request()->query('q', '');
        $term = trim(mb_substr(is_string($rawTerm) ? $rawTerm : '', 0, 90));
        $results = [];

        if ($term !== '') {
            $words = array_values(array_filter(preg_split('/\s+/u', $this->normalize($term)) ?: []));
            foreach ($topics as $slug => $topic) {
                $text = $this->normalize($topic['title'].' '.$topic['summary'].' '.$this->readTopic($slug));
                if (collect($words)->every(static fn (string $word): bool => str_contains($text, $word))) {
                    $results[$slug] = $topic;
                }
            }
        }

        // Static documentation written by the application developer, not editable user HTML.
        $html = Str::markdown($this->readTopic($selected), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        [$html, $headings] = $this->addHeadingAnchors($html);
        $slugs = array_keys($topics);
        $position = array_search($selected, $slugs, true);
        $previousSlug = $position > 0 ? $slugs[$position - 1] : null;
        $nextSlug = $position !== false && $position < count($slugs) - 1 ? $slugs[$position + 1] : null;

        return [
            'helpTopics' => $topics,
            'selectedTopic' => $selected,
            'activeTopic' => $topics[$selected],
            'helpHtml' => $html,
            'helpHeadings' => $headings,
            'previousTopicSlug' => $previousSlug,
            'nextTopicSlug' => $nextSlug,
            'searchTerm' => $term,
            'searchResults' => $results,
        ];
    }

    /**
     * Anchor only headings from already-sanitized, developer-controlled Markdown.
     * @return array{string, list<array{id: string, title: string, level: int}>}
     */
    private function addHeadingAnchors(string $html): array
    {
        $headings = [];
        $ids = [];
        $html = preg_replace_callback(
            '/<h([23])>(.*?)<\/h\1>/su',
            static function (array $matches) use (&$headings, &$ids): string {
                $title = trim(html_entity_decode(strip_tags($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $base = Str::slug($title) ?: 'secao';
                $ids[$base] = ($ids[$base] ?? 0) + 1;
                $id = $ids[$base] === 1 ? $base : $base.'-'.$ids[$base];
                $headings[] = ['id' => $id, 'title' => $title, 'level' => (int) $matches[1]];

                return '<h'.$matches[1].' id="'.$id.'">'.$matches[2].'</h'.$matches[1].'>';
            },
            $html,
        ) ?? $html;

        return [$html, $headings];
    }

    private function readTopic(string $slug): string
    {
        // Every built-in guide is bundled with PHP as well as provided in Markdown.
        // Some incremental installations may omit resources/help; the guide must still work.
        $bundled = BundledHelpArticles::get($slug);
        if ($bundled !== null && trim($bundled) !== '') {
            return $bundled;
        }

        // Allow new guides added by developers without rebuilding the original bundle.
        $filename = resource_path('help/'.$slug.'.md');
        if (is_file($filename) && is_readable($filename)) {
            $text = file_get_contents($filename);
            if (is_string($text) && trim($text) !== '') {
                return $text;
            }
        }

        return '## Não foi possível abrir este tutorial\n\nO conteúdo deste assunto não foi instalado corretamente. Contate o suporte técnico informando o nome do tutorial.';
    }

    private function normalize(string $text): string
    {
        return mb_strtolower(Str::ascii($text));
    }
}
