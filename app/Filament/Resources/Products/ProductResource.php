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
use App\Filament\Support\ProductVariationMatrix;
use App\Support\BrazilianMoney;
use App\Support\Catalog\ProductDescription;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Domain\Access\AdminPermissions;
use App\Filament\Support\RestrictsResourceByGroup;
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
use Illuminate\Support\Str;
use Livewire\Component;
use Filament\Schemas\Components\Utilities\Set;

final class ProductResource extends Resource
{
    use RestrictsResourceByGroup;

    protected static function permissionKey(): string
    {
        return AdminPermissions::CATALOG_PRODUCTS;
    }

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
                    ->formatStateUsing(fn (mixed $state): string => ProductDescription::html($state))
                    ->dehydrateStateUsing(fn (mixed $state): ?string => ProductDescription::html($state) !== '' ? ProductDescription::html($state) : null)
                    ->helperText('Formate o texto e use emojis. Scripts, HTML livre e anexos não são permitidos.')
                    ->columnSpanFull(),
            ]),
            Section::make('SEO')
                ->description('Opcional. Se ficar vazio, a loja usa automaticamente o nome e a descrição do produto.')
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    TextInput::make('seo_title')->label('Título SEO')->maxLength(255)->helperText('Idealmente até 60 caracteres.'),
                    Textarea::make('seo_description')->label('Descrição SEO')->maxLength(5000)->rows(3)->helperText('Idealmente até 160 caracteres.'),
                ]),
            Section::make('Mídia')->schema([
                self::uploads(),
                TextInput::make('youtube_url')->label('Vídeo demonstrativo do YouTube')->url()->maxLength(2048),
            ]),
            Section::make('Comercial')->columns(['default' => 1, 'lg' => 3])->schema([
                BrazilianMoneyInput::make('cost_price')->label('Preço de custo')->required()->live(onBlur: true),
                BrazilianMoneyInput::make('price')->label('Preço de venda')->required()->live(onBlur: true),
                BrazilianMoneyInput::make('promotional_price')->label('Preço promocional')->optionalPromotion()->helperText('Opcional. Deixe vazio ou 0,00 para vender sem promoção.')->live(onBlur: true),
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
            Section::make('Variações do produto')
                ->description('Para qualquer tipo de produto: cadastre as características que o comprador precisa escolher, gere as combinações e informe os preços e estoques. Exemplos: voltagem, capacidade, material, modelo, acabamento ou tamanho. Se não houver escolhas, deixe esta seção vazia.')
                ->collapsible()->schema([
                    Repeater::make('variation_axes')
                        ->label('1. Características que o comprador pode escolher')
                        ->addActionLabel('Adicionar característica')
                        ->defaultItems(0)->maxItems(ProductVariationMatrix::MAX_AXES)
                        ->reorderable(false)->collapsible()->columns(['default' => 1, 'md' => 2])
                        ->itemLabel(fn (array $state): string => trim((string) ($state['name'] ?? '')) ?: 'Nova característica')
                        ->saved(false)
                        ->schema([
                            TextInput::make('name')->label('Nome da característica')->required()->maxLength(120)
                                ->placeholder('Ex.: Voltagem, Capacidade, Modelo, Material')
                                ->helperText('Escreva o nome que quiser. Não existe uma lista fixa de características.'),
                            TagsInput::make('values')->label('Valores')->required()
                                ->placeholder('Digite um valor e pressione Enter')
                                ->helperText('Digite um valor por vez e pressione Enter. Ex.: 110 V e 220 V; 500 ml e 1 L; Alumínio e Inox.'),
                        ]),
                    Actions::make([
                        Action::make('generateProductCombinations')
                            ->label('2. Gerar / atualizar combinações')
                            ->icon('heroicon-o-squares-plus')->color('primary')
                            ->action(function (Get $schemaGet, Set $schemaSet): void {
                                // Removing the final characteristic must also clear the old table.
                                if (($schemaGet('variation_axes') ?? []) === []) {
                                    $schemaSet('variants', []);
                                    Notification::make()->success()->title('Produto sem variações')
                                        ->body('As opções foram retiradas da tabela. Salve o produto para desativá-las na loja.')->send();

                                    return;
                                }
                                try {
                                    $rows = ProductVariationMatrix::generate(
                                        $schemaGet('variation_axes') ?? [],
                                        $schemaGet('variants') ?? [],
                                    );
                                } catch (InvalidArgumentException $exception) {
                                    Notification::make()->danger()->title('Revise as opções')->body($exception->getMessage())->send();

                                    return;
                                }
                                $state = [];
                                foreach ($rows as $row) {
                                    $state[(string) Str::uuid()] = $row;
                                }
                                $schemaSet('variants', $state);
                                Notification::make()->success()->title(count($rows).' combinações prontas')
                                    ->body('Confira os preços e o estoque. As alterações só valem depois de salvar o produto.')->send();
                            }),
                        Action::make('clearAllProductVariations')
                            ->label('Remover todas as variações')
                            ->visible(fn (Get $get): bool => ($get('variation_axes') ?? []) !== [] || ($get('variants') ?? []) !== [])
                            ->icon('heroicon-o-trash')->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Vender este produto sem variações?')
                            ->modalDescription('Isso remove as opções desta tela. Ao salvar o produto, as combinações serão desativadas na loja. Pedidos anteriores são preservados; reservas ativas podem impedir a alteração.')
                            ->action(function (Set $schemaSet): void {
                                $schemaSet('variation_axes', []);
                                $schemaSet('variants', []);
                                Notification::make()->success()->title('Variações retiradas da tabela')
                                    ->body('Clique em Salvar para confirmar a alteração.')->send();
                            }),
                        Action::make('applyPriceStockToVariations')
                            ->label('Aplicar preço / estoque a todos')
                            ->icon('heroicon-o-arrow-down-on-square-stack')->color('gray')
                            ->schema([
                                BrazilianMoneyInput::make('price')->label('Preço próprio para todas')->helperText('Em branco: não altera os preços individuais.'),
                                TextInput::make('stock')->label('Estoque de cada combinação')->integer()->minValue(0)
                                    ->helperText('Em branco: não altera os estoques. Use 0 para marcar sem estoque.'),
                            ])
                            ->action(function (array $data, Get $schemaGet, Set $schemaSet): void {
                                $rows = $schemaGet('variants') ?? [];
                                if ($rows === []) {
                                    Notification::make()->warning()->title('Gere as combinações primeiro.')->send();

                                    return;
                                }
                                foreach ($rows as &$row) {
                                    if (($data['price'] ?? null) !== null) {
                                        $row['price'] = BrazilianMoney::format((int) $data['price']);
                                        // An old promotion might be higher than the new price.
                                        $row['promotional_price'] = null;
                                    }
                                    if (($data['stock'] ?? null) !== null && ($data['stock'] ?? '') !== '') {
                                        $row['stock'] = (int) $data['stock'];
                                    }
                                }
                                unset($row);
                                $schemaSet('variants', $rows);
                                Notification::make()->success()->title('Valores aplicados às combinações')->send();
                            }),
                    ]),
                    Repeater::make('variants')
                        ->label('3. Preço, estoque e SKU de cada opção')
                        ->helperText('Preço ou estoque em branco: usa o valor geral do produto. Use os ajustes (ícone de controles) para promoção, peso e medidas específicas.')
                        ->defaultItems(0)->maxItems(ProductVariationMatrix::MAX_ROWS)
                        ->addable(false)->reorderable(false)->deletable(false)
                        ->table([
                            TableColumn::make('Combinação')->width('200px'),
                            TableColumn::make('Preço próprio')->width('150px'),
                            TableColumn::make('Estoque')->width('110px'),
                            TableColumn::make('SKU (opcional)')->width('160px'),
                            TableColumn::make('Ativa')->width('80px'),
                        ])
                        ->compact()
                        ->schema([
                            Hidden::make('id'),
                            Hidden::make('attributes'),
                            Hidden::make('promotional_price'),
                            Hidden::make('weight'), Hidden::make('height'),
                            Hidden::make('width'), Hidden::make('length'),
                            TextInput::make('combination_display')->label('Combinação')->disabled()->saved(false),
                            BrazilianMoneyInput::make('price')->label('Preço próprio')->placeholder('Usar preço geral'),
                            TextInput::make('stock')->label('Estoque')->integer()->minValue(0)->placeholder('Geral'),
                            TextInput::make('sku')->label('SKU')->maxLength(255)->placeholder('Opcional'),
                            Toggle::make('is_active')->label('Ativa')->default(true),
                        ])
                        ->extraItemActions([
                            Action::make('configureVariationDetails')->label('Detalhes')
                                ->icon('heroicon-o-adjustments-horizontal')
                                ->modalHeading('Detalhes da combinação')
                                ->schema([
                                    BrazilianMoneyInput::make('promotional_price')->label('Preço promocional próprio')->optionalPromotion()
                                        ->helperText('Opcional. Deve ser menor que o preço de venda da combinação.'),
                                    Text::make('Se esta variação tem embalagem diferente, informe todas as quatro medidas. Caso contrário, deixe-as vazias para usar as do produto.'),
                                    TextInput::make('weight')->label('Peso (kg)')->numeric()->minValue(0)->step('0.001'),
                                    TextInput::make('height')->label('Altura (cm)')->numeric()->minValue(0)->step('0.01'),
                                    TextInput::make('width')->label('Largura (cm)')->numeric()->minValue(0)->step('0.01'),
                                    TextInput::make('length')->label('Comprimento (cm)')->numeric()->minValue(0)->step('0.01'),
                                ])
                                ->fillForm(function (array $arguments, Repeater $component): array {
                                    return array_intersect_key(
                                        $component->getRawItemState($arguments['item']) ?? [],
                                        array_flip(['promotional_price', 'weight', 'height', 'width', 'length']),
                                    );
                                })
                                ->action(function (array $data, array $arguments, Repeater $component): void {
                                    $rows = $component->getState();
                                    $key = $arguments['item'];
                                    if (! array_key_exists($key, $rows)) {
                                        return;
                                    }
                                    foreach (['promotional_price', 'weight', 'height', 'width', 'length'] as $name) {
                                        $rows[$key][$name] = $data[$name] ?? null;
                                    }
                                    $component->state($rows);
                                }),
                        ]),
                    Text::make('Para retirar um valor, exclua-o da característica e clique em Gerar / atualizar combinações. Para deixar o produto sem opções, use Remover todas as variações (ou retire a última característica e gere novamente). Depois clique em Salvar. Pedidos anteriores são preservados e reservas ativas podem impedir a exclusão.'),
                ]),
            Section::make('Disponibilidade')->columns(['default' => 1, 'lg' => 2])->schema([
                Select::make('availability')->label('Disponibilidade')->options(self::availabilityOptions())->required()->default(ProductAvailability::IMMEDIATE->value),
                TextInput::make('stock')->label('Estoque')->integer()->minValue(0),
            ]),
            Section::make('Dados para entrega')
                ->description('Informe peso e dimensões da unidade embalada para o cálculo do frete. Use os dados reais do produto, independentemente do segmento.')
                ->columns(['default' => 1, 'lg' => 4])->schema([
                    TextInput::make('weight')->label('Peso embalado')->suffix('kg')->numeric()->minValue(0)->step('0.001'),
                    TextInput::make('height')->label('Altura da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                    TextInput::make('width')->label('Largura da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
                    TextInput::make('length')->label('Comprimento da embalagem')->suffix('cm')->numeric()->minValue(0)->step('0.01'),
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
