<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Domain\Catalog\Models\Category;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Livewire\Attributes\On;

trait HandlesProductAiAssistant
{
    protected function productAiAssistantAction(): Action
    {
        return Action::make('productAiAssistant')
            ->label('Assistente de IA')
            ->icon('heroicon-o-sparkles')
            ->color('gray')
            ->modalHeading('Assistente de IA para o produto')
            ->modalDescription('A IA sugere textos; você escolhe o que aplicar. Nenhuma alteração é salva automaticamente.')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalContent(fn () => view('filament.components.product-ai-assistant', [
                'initial' => $this->productAiInitialState(),
                'productId' => isset($this->record) && $this->record?->exists ? (int) $this->record->getKey() : null,
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->closeModalByClickingAway(false)
            ->stickyModalHeader()
            ->stickyModalFooter();
    }

    #[On('product-ai-suggestions-applied')]
    public function applyProductAiSuggestions(array $fields): void
    {
        $allowed = ['name', 'short_description', 'description', 'seo_title', 'seo_description', 'category_id'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $fields)) {
                $this->data[$field] = $fields[$field];
            }
        }

        Notification::make()
            ->success()
            ->title('Sugestões aplicadas ao formulário')
            ->body('Revise os campos e clique em salvar para gravar as alterações.')
            ->send();
    }

    /** @return array<string, mixed> */
    private function productAiInitialState(): array
    {
        $state = $this->data ?? [];
        $categoryName = null;
        if (filled($state['category_id'] ?? null)) {
            $categoryName = Category::query()->whereKey((int) $state['category_id'])->value('name');
        }

        return [
            'name' => $state['name'] ?? null,
            'short_description' => $state['short_description'] ?? null,
            'description' => $state['description'] ?? null,
            'seo_title' => $state['seo_title'] ?? null,
            'seo_description' => $state['seo_description'] ?? null,
            'sku' => $state['sku'] ?? null,
            'category' => $categoryName,
            'category_id' => $state['category_id'] ?? null,
            'variants' => collect($state['variants'] ?? [])->map(function (mixed $variant): array {
                if (! is_array($variant)) {
                    return [];
                }

                return ['attributes' => is_array($variant['attributes'] ?? null) ? $variant['attributes'] : []];
            })->filter()->values()->all(),
        ];
    }
}
