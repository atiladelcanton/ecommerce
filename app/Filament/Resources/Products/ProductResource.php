<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products;

use App\Actions\Catalog\DeleteProductAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\ValueObjects\ProductPricing;
use App\Filament\Forms\Components\BrazilianMoneyInput;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Filament\Support\CatalogForm;
use App\Support\BrazilianMoney;
use App\Support\Catalog\ProductDescription;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Livewire\Component;

final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 2;

    public static function availabilityOptions(): array
    {
        $options = [];
        foreach (ProductAvailability::cases() as $availability) {
            $options[$availability->value] = $availability->label();
        }

        return $options;
    }

    public static function uploads(): FileUpload
    {
        return FileUpload::make('new_images')->label('Adicionar fotos')->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->disk('public')->directory('products')->visibility('public')
            ->multiple()->reorderable()->maxSize(5120)
            ->preventFilePathTampering(allowFilePathUsing: fn (string $file, Component $livewire): bool => in_array($file, $livewire->pendingImagePaths, true))
            ->helperText('JPG, PNG ou WebP, até 5 MB por foto. Salve para adicionar à galeria. A primeira foto será a principal; gerencie a galeria abaixo após salvar.')
            ->columnSpanFull();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações')->columns(['default' => 1, 'lg' => 2])->schema([
                TextInput::make('name')->label('Nome')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->maxLength(255)->helperText('Deixe vazio ao criar para gerar a partir do nome.'),
                TextInput::make('sku')->label('SKU')->maxLength(255),
                Select::make('category_id')->label('Categoria')->relationship('category', 'name')->searchable()->required(),
                Textarea::make('short_description')->label('Descrição curta')->columnSpanFull(),
                RichEditor::make('description')->label('Descrição')
                    ->toolbarButtons([
                        ['h2', 'h3', 'bold', 'italic', 'underline'],
                        ['bulletList', 'orderedList', 'blockquote', 'link'],
                        ['undo', 'redo'],
                    ])
                    ->formatStateUsing(fn (?string $state): string => ProductDescription::html($state))
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? ProductDescription::html($state) : null)
                    ->helperText('Formate o texto e use emojis. Scripts, HTML livre e anexos não são permitidos.')
                    ->columnSpanFull(),
            ]),
            Section::make('Mídia')->schema([
                self::uploads(),
                TextInput::make('youtube_url')->label('Vídeo demonstrativo do YouTube')->url()->maxLength(2048),
            ]),
            Section::make('Comercial')->columns(['default' => 1, 'lg' => 3])->schema([
                BrazilianMoneyInput::make('cost_price')->label('Preço de custo')->required()->live(onBlur: true),
                BrazilianMoneyInput::make('price')->label('Preço de venda')->required()->live(onBlur: true),
                BrazilianMoneyInput::make('promotional_price')->label('Preço promocional')->live(onBlur: true),
                Text::make(function (Get $get): string {
                    try {
                        $price = BrazilianMoney::parse($get('price'));
                        if ($price === null) {
                            return '';
                        }
                        $pricing = new ProductPricing($price, BrazilianMoney::parse($get('promotional_price')));

                        return $pricing->hasPromotion() ? 'Promoção válida: '.$pricing->discountPercentage().'% de desconto.' : '';
                    } catch (InvalidArgumentException) {
                        return '';
                    }
                })->color('success')->columnSpanFull(),
            ]),
            Section::make('Variações')->description('Cadastre cada combinação vendável, como Cor + Embalagem ou Poliéster + Tamanho. Valores vazios herdam o produto. Remover uma linha desativa a variação existente.')->schema([
                Repeater::make('variants')->label('Combinações')->defaultItems(0)->addActionLabel('Adicionar variação')
                    ->itemLabel(fn (array $state): string => implode(' / ', array_values($state['attributes'] ?? [])) ?: 'Nova variação')
                    ->collapsible()->columns(2)->schema([
                        Hidden::make('id'),
                        KeyValue::make('attributes')->label('Características')->keyLabel('Característica')->valueLabel('Valor')->addActionLabel('Adicionar característica')->required()->columnSpanFull(),
                        TextInput::make('sku')->label('SKU da variação')->maxLength(255),
                        Toggle::make('is_active')->label('Variação ativa')->default(true),
                        BrazilianMoneyInput::make('price')->label('Preço da variação')->helperText('Vazio: herda o preço do produto.'),
                        BrazilianMoneyInput::make('promotional_price')->label('Preço promocional da variação'),
                        TextInput::make('stock')->label('Estoque da variação')->integer()->minValue(0)->helperText('Vazio: compartilha o estoque geral do produto.'),
                        TextInput::make('weight')->label('Peso da embalagem')->suffix('kg')->numeric()->minValue(0)->step('0.001'),
                        TextInput::make('height')->label('Altura da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                        TextInput::make('width')->label('Largura da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                        TextInput::make('length')->label('Comprimento da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                    ]),
            ]),
            Section::make('Disponibilidade')->columns(['default' => 1, 'lg' => 2])->schema([
                Select::make('availability')->label('Disponibilidade')->options(self::availabilityOptions())->required()->default(ProductAvailability::IMMEDIATE->value),
                TextInput::make('stock')->label('Estoque')->integer()->minValue(0),
            ]),
            Section::make('Transporte')->columns(['default' => 1, 'lg' => 4])->schema([
                TextInput::make('weight')->label('Peso')->suffix('kg')->numeric()->minValue(0)->step('0.001'),
                TextInput::make('height')->label('Altura')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                TextInput::make('width')->label('Largura')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                TextInput::make('length')->label('Comprimento')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
            ]),
            Section::make('Publicação')->columns(['default' => 1, 'lg' => 3])->schema([
                Toggle::make('is_active')->label('Ativo')->default(true),
                Toggle::make('is_featured')->label('Produto em destaque')->default(false),
                Toggle::make('whatsapp_quote_enabled')->label('Permitir cotação pelo WhatsApp')->default(true),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['category', 'images' => fn (HasMany $query): HasMany => $query->where('is_primary', true)]))
            ->columns([
                ImageColumn::make('primary_image')->label('Foto principal')->state(fn (Product $record): ?string => $record->images->first()?->path)->disk('public')->visibility('public'),
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('category.name')->label('Categoria')->sortable(),
                TextColumn::make('availability')->label('Disponibilidade')->badge()
                    ->formatStateUsing(fn (ProductAvailability $state): string => $state->label())
                    ->color(fn (ProductAvailability $state): string => match ($state) {
                        ProductAvailability::IMMEDIATE => 'success',
                        ProductAvailability::ON_DEMAND => 'warning',
                        ProductAvailability::UNAVAILABLE => 'danger',
                    }),
                TextColumn::make('price')->label('Preço')->formatStateUsing(fn (int $state): string => BrazilianMoney::display($state))->sortable(),
                TextColumn::make('promotional_price')->label('Preço promocional')->placeholder('—')->badge()->color('success')
                    ->formatStateUsing(fn (int $state): string => BrazilianMoney::display($state)),
                IconColumn::make('is_active')->label('Ativo')->boolean(),
                IconColumn::make('is_featured')->label('Destaque')->boolean(),
            ])->filters([
                SelectFilter::make('category_id')->label('Categoria')->relationship('category', 'name')->searchable(),
                SelectFilter::make('availability')->label('Disponibilidade')->options(self::availabilityOptions()),
                TernaryFilter::make('is_active')->label('Ativo')->trueLabel('Ativos')->falseLabel('Inativos'),
                TernaryFilter::make('is_featured')->label('Destaque')->trueLabel('Em destaque')->falseLabel('Sem destaque'),
                Filter::make('on_promotion')->label('Em promoção')->query(fn (Builder $query): Builder => $query->onPromotion()),
            ])->recordActions([
                EditAction::make()->iconButton()->tooltip('Editar'),
                CatalogForm::delete(fn (Product $record) => app(DeleteProductAction::class)->execute($record))->iconButton()->tooltip('Excluir'),
            ])->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [ImagesRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => ListProducts::route('/'), 'create' => CreateProduct::route('/create'), 'edit' => EditProduct::route('/{record}/edit')];
    }
}
