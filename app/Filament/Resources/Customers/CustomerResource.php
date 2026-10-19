<?php

declare(strict_types=1);

namespace App\Filament\Resources\Customers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Support\BrazilianData;
use App\Support\BrazilianMoney;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withCount('orders')
                ->withSum([
                    'orders as paid_orders_total_cents' => fn (Builder $orders): Builder => $orders->where('payment_status', PaymentStatus::APPROVED->value),
                ], 'total_cents')
                ->withMax('orders', 'created_at'))
            ->columns([
                TextColumn::make('name')->label('Cliente')->searchable()->sortable()->weight('semibold')
                    ->description(fn (Customer $record): string => $record->email),
                TextColumn::make('phone')->label('Telefone')->formatStateUsing(fn (?string $state): string => BrazilianData::phone($state ?? ''))->searchable(),
                TextColumn::make('document')->label('CPF/CNPJ')->formatStateUsing(fn (?string $state): string => BrazilianData::document($state ?? ''))->searchable(),
                TextColumn::make('birth_date')->label('Nascimento')->date('d/m/Y')->placeholder('Não informado')->sortable(),
                TextColumn::make('orders_count')->label('Pedidos')->numeric()->alignCenter()->sortable(),
                TextColumn::make('paid_orders_total_cents')->label('Compras pagas')
                    ->formatStateUsing(fn ($state): string => BrazilianMoney::display((int) ($state ?? 0)))->sortable(),
                TextColumn::make('orders_max_created_at')->label('Último pedido')->dateTime('d/m/Y H:i')->placeholder('Sem pedidos')->sortable(),
            ])
            ->filters([
                Filter::make('birthdays_this_month')
                    ->label('Aniversariantes do mês')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('birth_date')->whereMonth('birth_date', now()->month)),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver cliente')->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
