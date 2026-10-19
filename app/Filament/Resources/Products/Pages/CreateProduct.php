<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Support\HandlesProductAiAssistant;
use App\Filament\Support\HandlesProductForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateProduct extends CreateRecord
{
    use HandlesProductAiAssistant, HandlesProductForm;

    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->saveProductForm($data);
    }

    protected function getHeaderActions(): array
    {
        return [$this->productAiAssistantAction()];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
