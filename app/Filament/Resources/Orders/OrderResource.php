<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Support\BrazilianMoney;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('items')->with(['customer']))
            ->columns([
                TextColumn::make('number')->label('Pedido')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('customer_name')->label('Cliente')->searchable(['customer_name', 'customer_email', 'customer_document'])->description(fn (Order $record): string => $record->customer_email)
                    ->url(fn (Order $record): ?string => $record->customer ? CustomerResource::getUrl('view', ['record' => $record->customer]) : null),
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('items_count')->label('Itens')->numeric()->alignCenter(),
                TextColumn::make('total_cents')->label('Total')->placeholder('A definir')->formatStateUsing(fn (int $state): string => BrazilianMoney::display($state))->sortable(),
                TextColumn::make('payment_status')->label('Pagamento')->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::APPROVED => 'success',
                        PaymentStatus::PENDING => 'warning',
                        PaymentStatus::FAILED, PaymentStatus::CANCELLED => 'danger',
                        PaymentStatus::REFUNDED => 'gray',
                    }),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color()),
                TextColumn::make('shipping_pricing_status')->label('Cotação do frete')->badge()->formatStateUsing(fn ($state): string => $state->label())->placeholder('Entrega definida'),
                TextColumn::make('shipping_name')->label('Entrega')->wrap(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(self::statusOptions()),
                SelectFilter::make('payment_status')->label('Pagamento')->options(self::paymentOptions()),
                SelectFilter::make('shipping_provider')->label('Meio de entrega')->options([
                    'manual' => 'Entrega negociada',
                    'fake' => 'Entrega demonstrativa',
                    'melhor_envio' => 'Melhor Envio',
                    'local_pickup' => 'Retirada na loja',
                    'superfrete' => 'SuperFrete',
                    'correios' => 'Correios',
                ]),
                Filter::make('period')->label('Período')->schema([
                    DatePicker::make('from')->label('De'),
                    DatePicker::make('until')->label('Até'),
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date));
                }),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver pedido')->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        $options = [];
        foreach (OrderStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    /** @return array<string, string> */
    public static function paymentOptions(): array
    {
        $options = [];
        foreach (PaymentStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
