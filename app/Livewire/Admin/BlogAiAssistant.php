<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\AI\GenerateBlogContentAction;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use App\Support\Catalog\ProductDescription;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class BlogAiAssistant extends Component
{
    /** @var array<string, mixed> */
    public array $initial = [];

    public string $mode = 'generate';

    public string $brief = '';

    public string $tone = 'didático';

    public string $length = 'médio';

    /** @var array<string, string|null>|null */
    public ?array $suggestion = null;

    /** @var array<string, bool> */
    public array $applyFields = [
        'title' => true,
        'excerpt' => true,
        'content' => true,
        'seo_title' => true,
        'seo_description' => true,
    ];

    public ?string $errorMessage = null;

    public function mount(array $initial = []): void
    {
        $this->initial = $initial;
        $this->mode = filled($initial['content'] ?? null) || filled($initial['excerpt'] ?? null) ? 'improve' : 'generate';
    }

    public function generate(): void
    {
        abort_unless(auth('web')->check(), 403);
        $this->errorMessage = null;
        $this->suggestion = null;
        try {
            $this->suggestion = app(GenerateBlogContentAction::class)->execute(
                $this->mode, $this->brief, $this->initial, $this->tone, $this->length,
            );
        } catch (ValidationException $exception) {
            $this->errorMessage = (string) collect($exception->errors())->flatten()->first();
        } catch (AiProviderFailed $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    public function apply(): void
    {
        abort_unless(auth('web')->check(), 403);
        if ($this->suggestion === null) {
            return;
        }
        $fields = [];
        foreach (['title', 'excerpt', 'content', 'seo_title', 'seo_description'] as $field) {
            if (($this->applyFields[$field] ?? false) && filled($this->suggestion[$field] ?? null)) {
                $fields[$field] = $this->suggestion[$field];
            }
        }
        if ($fields === []) {
            $this->errorMessage = 'Selecione pelo menos um campo para aplicar.';
            return;
        }
        $this->dispatch('blog-ai-suggestions-applied', fields: $fields);
        $this->initial = array_merge($this->initial, $fields);
        $this->errorMessage = null;
        $this->suggestion = null;
    }

    public function resetSuggestion(): void
    {
        $this->suggestion = null;
        $this->errorMessage = null;
    }

    public function render(): View
    {
        return view('livewire.admin.blog-ai-assistant', [
            'configured' => AiSetting::current()->isConfigured(),
            'settingsUrl' => url('/admin/ai-settings'),
            'suggestedHtml' => ProductDescription::html($this->suggestion['content'] ?? null),
            'currentHtml' => ProductDescription::html($this->initial['content'] ?? null),
        ]);
    }
}
