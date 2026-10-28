<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Livewire\Attributes\On;

trait HandlesBlogAiAssistant
{
    protected function blogAiAssistantAction(): Action
    {
        return Action::make('blogAiAssistant')
            ->label('Assistente de IA')
            ->icon('heroicon-o-sparkles')
            ->color('gray')
            ->modalHeading('Assistente de IA para o blog')
            ->modalDescription('Escreva um artigo ou melhore um rascunho. A IA não salva nem publica a postagem.')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalContent(fn () => view('filament.components.blog-ai-assistant', [
                'initial' => $this->blogAiInitialState(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->closeModalByClickingAway(false)
            ->stickyModalHeader()
            ->stickyModalFooter();
    }

    #[On('blog-ai-suggestions-applied')]
    public function applyBlogAiSuggestions(array $fields): void
    {
        // Permit only editorial fields; never mutate status, date, cover, slug or author.
        foreach (['title', 'excerpt', 'content', 'seo_title', 'seo_description'] as $field) {
            if (isset($fields[$field]) && is_string($fields[$field])) {
                $this->data[$field] = $fields[$field];
            }
        }
        Notification::make()->success()
            ->title('Sugestões aplicadas ao formulário')
            ->body('Revise o artigo e clique em Salvar. Nenhuma publicação é automática.')
            ->send();
    }

    /** @return array<string, mixed> */
    private function blogAiInitialState(): array
    {
        $state = $this->data ?? [];

        return [
            'title' => $state['title'] ?? null,
            'excerpt' => $state['excerpt'] ?? null,
            'content' => $state['content'] ?? null,
            'category' => $state['category'] ?? null,
            'seo_title' => $state['seo_title'] ?? null,
            'seo_description' => $state['seo_description'] ?? null,
        ];
    }
}
