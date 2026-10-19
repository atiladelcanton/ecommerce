<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Actions\AI\GenerateProductContentAction;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use App\Support\Catalog\ProductDescription;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class ProductAiAssistant extends Component
{
    /** @var array<string, mixed> */
    public array $initial = [];

    public ?int $productId = null;

    public string $mode = 'improve';

    public string $brief = '';

    /** @var array<string, mixed>|null */
    public ?array $suggestion = null;

    /** @var array<string, bool> */
    public array $applyFields = [
        'name' => true,
        'short_description' => true,
        'description' => true,
        'seo_title' => true,
        'seo_description' => true,
        'category_id' => false,
    ];

    public ?string $errorMessage = null;

    public function mount(array $initial = [], ?int $productId = null): void
    {
        $this->initial = $initial;
        $this->productId = $productId;
        $this->mode = collect(['name', 'short_description', 'description'])
            ->contains(fn (string $field): bool => filled($initial[$field] ?? null)) ? 'improve' : 'generate';
    }

    public function generate(): void
    {
        abort_unless(auth('web')->check(), 403);
        $this->errorMessage = null;
        $this->suggestion = null;

        try {
            $this->suggestion = app(GenerateProductContentAction::class)->execute(
                mode: $this->mode,
                brief: $this->brief,
                current: $this->initial,
                productId: $this->productId,
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
        foreach ($this->applyFields as $field => $enabled) {
            if (! $enabled || ! array_key_exists($field, $this->suggestion)) {
                continue;
            }
            $value = $this->suggestion[$field];
            if ($value === null || $value === '') {
                continue;
            }
            $fields[$field] = $value;
        }

        if ($fields === []) {
            $this->errorMessage = 'Selecione pelo menos uma sugestão para aplicar.';

            return;
        }

        $this->dispatch('product-ai-suggestions-applied', fields: $fields);
        $this->dispatch('product-ai-applied');
        $this->initial = array_merge($this->initial, $fields);
        $this->errorMessage = null;
    }

    public function resetSuggestion(): void
    {
        $this->suggestion = null;
        $this->errorMessage = null;
    }

    public function render(): View
    {
        return view('livewire.admin.product-ai-assistant', [
            'configured' => AiSetting::current()->isConfigured(),
            'settingsUrl' => url('/admin/ai-settings'),
            'currentDescription' => ProductDescription::html($this->initial['description'] ?? null),
            'suggestedDescription' => ProductDescription::html($this->suggestion['description'] ?? null),
        ]);
    }
}
