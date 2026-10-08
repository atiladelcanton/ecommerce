<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\RelationManagers;

use App\Actions\Catalog\RemoveProductImageAction;
use App\Actions\Catalog\SetPrimaryProductImageAction;
use App\Actions\Catalog\UpdateProductImageOrderAction;
use App\Domain\Catalog\Models\ProductImage;
use App\Filament\Support\CatalogForm;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Galeria de fotos';

    #[On('gallery-updated')]
    public function refreshGallery(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table->heading('Galeria de fotos')->description('A imagem principal aparece primeiro. As demais seguem a ordem numérica, da menor para a maior.')
            ->columns([
                ImageColumn::make('path')->label('Foto')->disk('public')->visibility('public'),
                IconColumn::make('is_primary')->label('Principal')->boolean(),
                TextColumn::make('sort_order')->label('Ordem'),
            ])->recordActions([
                Action::make('primary')->label('Definir principal')->icon('heroicon-o-star')
                    ->hidden(fn (ProductImage $record): bool => $record->is_primary)
                    ->action(fn (ProductImage $record) => app(SetPrimaryProductImageAction::class)->execute($record)),
                Action::make('order')->label('Alterar ordem')->icon('heroicon-o-arrows-up-down')
                    ->fillForm(fn (ProductImage $record): array => ['sort_order' => $record->sort_order])
                    ->schema([
                        Section::make('Ordenação da foto')->schema([
                            TextInput::make('sort_order')->label('Ordem')->integer()->required()->minValue(-2147483648)->maxValue(2147483647),
                        ]),
                    ])
                    ->action(fn (ProductImage $record, array $data) => app(UpdateProductImageOrderAction::class)->execute($record, (int) $data['sort_order'])),
                CatalogForm::delete(fn (ProductImage $record) => app(RemoveProductImageAction::class)->execute($record))
                    ->modalDescription('Excluir esta foto da galeria? O arquivo físico será preservado.'),
            ]);
    }
}
