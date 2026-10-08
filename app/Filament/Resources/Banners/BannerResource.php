<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners;

use App\Actions\Catalog\DeleteBannerAction;
use App\Domain\Catalog\Models\Banner;
use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Filament\Support\CatalogForm;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

final class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $modelLabel = 'banner';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    private static function image(string $field, string $label): FileUpload
    {
        return FileUpload::make($field)->label($label)->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->disk('public')->directory('banners')->visibility('public')->maxSize(5120)
            ->preventFilePathTampering(allowFilePathUsing: fn (string $file, Component $livewire): bool => in_array($file, $livewire->pendingBannerPaths, true))
            ->helperText('JPG, PNG ou WebP, até 5 MB.')->columnSpanFull();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informações')->columns(['default' => 1, 'lg' => 2])->schema([
                TextInput::make('title')->label('Título')->required()->maxLength(255),
                TextInput::make('link')->label('Link')->url()->maxLength(2048)->placeholder('https://'),
                TextInput::make('alt_text')->label('Texto alternativo')->maxLength(255)->helperText('Descreva a imagem para acessibilidade.')->columnSpanFull(),
            ]),
            Section::make('Imagens')->schema([
                self::image('desktop_image', 'Imagem desktop')->required(),
                self::image('mobile_image', 'Imagem mobile'),
            ]),
            Section::make('Publicação')->columns(['default' => 1, 'lg' => 2])->schema([
                Toggle::make('is_active')->label('Ativo')->default(true),
                TextInput::make('sort_order')->label('Ordem')->integer()->required()->default(0),
                DateTimePicker::make('starts_at')->label('Início')->seconds(false)->helperText('Opcional. Horário de Brasília.'),
                DateTimePicker::make('ends_at')->label('Fim')->seconds(false)->helperText('Opcional. Horário de Brasília.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('desktop_image')->label('Imagem desktop')->disk('public')->visibility('public'),
            TextColumn::make('title')->label('Título')->searchable()->sortable(),
            IconColumn::make('is_active')->label('Ativo')->boolean(),
            TextColumn::make('sort_order')->label('Ordem')->sortable(),
            TextColumn::make('starts_at')->label('Início')->dateTime('d/m/Y H:i')->placeholder('Sem início definido'),
            TextColumn::make('ends_at')->label('Fim')->dateTime('d/m/Y H:i')->placeholder('Sem fim definido'),
            TextColumn::make('updated_at')->label('Atualizado em')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([
            TernaryFilter::make('is_active')->label('Situação')->trueLabel('Ativo')->falseLabel('Inativo')->placeholder('Todos'),
            Filter::make('currently_visible')->label('Vigente')->query(fn (Builder $query): Builder => $query->currentlyVisible()),
        ])->defaultSort(fn (Builder $query): Builder => $query->ordered())->recordActions([
            EditAction::make()->iconButton()->tooltip('Editar'),
            CatalogForm::delete(fn (Banner $record) => app(DeleteBannerAction::class)->execute($record))->iconButton()->tooltip('Excluir'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListBanners::route('/'), 'create' => CreateBanner::route('/create'), 'edit' => EditBanner::route('/{record}/edit')];
    }
}
