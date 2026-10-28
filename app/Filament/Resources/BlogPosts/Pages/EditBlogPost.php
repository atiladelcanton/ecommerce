<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Actions\Blog\DeleteBlogPostAction;
use App\Actions\Blog\SaveBlogPostAction;
use App\Domain\Blog\Models\BlogPost;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Support\HandlesBlogAiAssistant;
use App\Filament\Support\CatalogForm;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

final class EditBlogPost extends EditRecord
{
    use HandlesBlogAiAssistant;

    protected static string $resource = BlogPostResource::class;

    #[Locked]
    public array $pendingBlogPaths = [];

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->pendingBlogPaths = array_values(array_filter([$data['cover_image'] ?? null]));

        return CatalogForm::submit(fn (): Model => app(SaveBlogPostAction::class)->execute($data, $record));
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->blogAiAssistantAction(),
            Action::make('viewPublic')->label('Ver no site')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->getRecord()->publicUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'published' && $this->getRecord()->published_at?->isPast() && (bool) \App\Domain\Settings\Models\StoreSetting::query()->find(1)?->blog_enabled),
            CatalogForm::delete(fn (BlogPost $record) => app(DeleteBlogPostAction::class)->execute($record)),
        ];
    }
}
