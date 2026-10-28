<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Actions\Blog\SaveBlogPostAction;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Support\HandlesBlogAiAssistant;
use App\Filament\Support\CatalogForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

final class CreateBlogPost extends CreateRecord
{
    use HandlesBlogAiAssistant;

    protected static string $resource = BlogPostResource::class;

    #[Locked]
    public array $pendingBlogPaths = [];

    protected function handleRecordCreation(array $data): Model
    {
        $this->pendingBlogPaths = array_values(array_filter([$data['cover_image'] ?? null]));

        return CatalogForm::submit(fn (): Model => app(SaveBlogPostAction::class)->execute($data));
    }

    protected function getHeaderActions(): array
    {
        return [$this->blogAiAssistantAction()];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
