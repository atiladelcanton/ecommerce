<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Coupon\Models\Coupon;
use App\Filament\Forms\Components\BrazilianMoneyInput;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Support\BrazilianMoney;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $modelLabel = 'cupom';
    protected static ?string $pluralModelLabel = 'Cupons de desconto';
    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informações do cupom')->columns(['default' => 1, 'lg' => 2])->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(64),
                TextInput::make('description')->label('Descrição interna')->maxLength(255),
                Select::make('discount_type')->label('Tipo de desconto')
                    ->options(['percentage' => 'Percentual', 'fixed' => 'Valor fixo'])
                    ->required()->live()->default('percentage'),
                TextInput::make('percentage_value')->label('Percentual (%)')->integer()->minValue(1)->maxValue(100)
                    ->required()->visible(fn (Get $get): bool => $get('discount_type') === 'percentage'),
                // O banco usa uma única coluna value para percentual ou centavos.
                BrazilianMoneyInput::make('fixed_value_cents')->label('Valor fixo do desconto')
                    ->required()->visible(fn (Get $get): bool => $get('discount_type') === 'fixed'),
                Select::make('scope')->label('Aplicável a')->options([
                    'all' => 'Todos os produtos', 'categories' => 'Categorias selecionadas', 'products' => 'Produtos selecionados',
                ])->default('all')->required()->live(),
                Select::make('category_ids')->label('Categorias contempladas')->multiple()->searchable()
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (Get $get): bool => $get('scope') === 'categories')
                    ->required(fn (Get $get): bool => $get('scope') === 'categories')->columnSpanFull(),
                Select::make('product_ids')->label('Produtos contemplados')->multiple()->searchable()
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (Get $get): bool => $get('scope') === 'products')
                    ->required(fn (Get $get): bool => $get('scope') === 'products')->columnSpanFull(),
                Toggle::make('allow_sale_items')->label('Permitir produtos já em promoção')->default(false),
            ]),
            Section::make('Limites e validade')->columns(['default' => 1, 'lg' => 2])->schema([
                BrazilianMoneyInput::make('minimum_subtotal_cents')->label('Subtotal mínimo')->helperText('Calculado sobre os produtos, sem frete.'),
                BrazilianMoneyInput::make('maximum_discount_cents')->label('Desconto máximo')->helperText('Opcional, em reais.'),
                TextInput::make('usage_limit')->label('Limite total de utilizações')->integer()->minValue(1),
                DateTimePicker::make('starts_at')->label('Válido a partir de')->seconds(false),
                DateTimePicker::make('ends_at')->label('Válido até')->seconds(false),
                Toggle::make('is_active')->label('Cupom ativo')->default(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable()->weight('bold'),
            TextColumn::make('discount_type')->label('Tipo')->formatStateUsing(fn (string $state): string => $state === 'percentage' ? 'Percentual' : 'Valor fixo'),
            TextColumn::make('value')->label('Desconto')->formatStateUsing(fn ($state, Coupon $record): string => $record->discount_type === 'percentage' ? $record->value.'%' : BrazilianMoney::display($record->value)),
            TextColumn::make('scope')->label('Aplicação')->formatStateUsing(fn (string $state): string => match ($state) { 'categories' => 'Categorias', 'products' => 'Produtos', default => 'Toda a loja' }),
            TextColumn::make('claims_count')->counts('claims')->label('Usos e reservas'),
            TextColumn::make('usage_limit')->label('Limite')->placeholder('Sem limite'),
            IconColumn::make('is_active')->label('Ativo')->boolean(),
            TextColumn::make('ends_at')->label('Expira em')->dateTime('d/m/Y H:i')->placeholder('Sem vencimento'),
        ])->filters([
            TernaryFilter::make('is_active')->label('Situação')->trueLabel('Ativo')->falseLabel('Inativo')->placeholder('Todos'),
        ])->defaultSort('created_at', 'desc')->recordActions([
            EditAction::make()->iconButton()->tooltip('Editar cupom'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCoupons::route('/'), 'create' => CreateCoupon::route('/create'), 'edit' => EditCoupon::route('/{record}/edit')];
    }
}
