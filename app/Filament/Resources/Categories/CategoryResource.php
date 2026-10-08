<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Actions\Catalog\DeleteCategoryAction;
use App\Domain\Catalog\Models\Category;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Support\CatalogForm;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $modelLabel = 'categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informações da categoria')
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    TextInput::make('name')->label('Nome')->required()->maxLength(255),
                    TextInput::make('slug')->label('Slug')->maxLength(255)->helperText('Deixe vazio ao criar para gerar a partir do nome.'),
                    Textarea::make('description')->label('Descrição')->columnSpanFull(),
                    TextInput::make('sort_order')->label('Ordem')->integer()->required()->default(0),
                    Toggle::make('is_active')->label('Ativo')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('slug')->label('Slug'),
            TextColumn::make('products_count')->label('Quantidade de produtos')->counts('products'),
            IconColumn::make('is_active')->label('Ativo')->boolean(),
            TextColumn::make('sort_order')->label('Ordem')->sortable(),
            TextColumn::make('updated_at')->label('Atualizado em')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([
            TernaryFilter::make('is_active')->label('Situação')->trueLabel('Ativo')->falseLabel('Inativo')->placeholder('Todas'),
        ])->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'))
            ->recordActions([
                EditAction::make()->iconButton()->tooltip('Editar'),
                CatalogForm::delete(fn (Category $record) => app(DeleteCategoryAction::class)->execute($record))->iconButton()->tooltip('Excluir'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCategories::route('/'), 'create' => CreateCategory::route('/create'), 'edit' => EditCategory::route('/{record}/edit')];
    }
}
