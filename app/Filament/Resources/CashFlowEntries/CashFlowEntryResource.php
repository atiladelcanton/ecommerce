<?php

declare(strict_types=1);

namespace App\Filament\Resources\CashFlowEntries;

use App\Actions\Finance\DeleteCashFlowEntryAction;
use App\Domain\Finance\Enums\CashFlowType;
use App\Domain\Finance\Models\CashFlowEntry;
use App\Filament\Forms\Components\BrazilianMoneyInput;
use App\Filament\Resources\CashFlowEntries\Pages\CreateCashFlowEntry;
use App\Filament\Resources\CashFlowEntries\Pages\EditCashFlowEntry;
use App\Filament\Resources\CashFlowEntries\Pages\ListCashFlowEntries;
use App\Filament\Resources\Orders\OrderResource;
use App\Support\BrazilianMoney;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class CashFlowEntryResource extends Resource
{
    protected static ?string $model = CashFlowEntry::class;

    protected static ?string $modelLabel = 'lançamento';

    protected static ?string $pluralModelLabel = 'Fluxo de Caixa';

    protected static string|\UnitEnum|null $navigationGroup = 'Financeiro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('cash_flow_entries');
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lançamento')->columns(['default' => 1, 'lg' => 3])->schema([
                Select::make('type')->label('Tipo')->options([
                    CashFlowType::INCOME->value => CashFlowType::INCOME->label(),
                    CashFlowType::EXPENSE->value => CashFlowType::EXPENSE->label(),
                ])->required(),
                TextInput::make('category')->label('Categoria')->required()->maxLength(80),
                DateTimePicker::make('occurred_at')->label('Data')->seconds(false)->required()->default(now()),
                TextInput::make('description')->label('Descrição')->required()->maxLength(255)->columnSpan(['lg' => 2]),
                BrazilianMoneyInput::make('amount_cents')->label('Valor')->required(),
                Textarea::make('notes')->label('Observações')->columnSpanFull(),
            ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('description')->label('Descrição')->searchable()->weight('semibold')->description(fn (CashFlowEntry $record): string => $record->source === 'payment' ? 'Gerado automaticamente pelo pagamento' : 'Lançamento manual'),
                TextColumn::make('category')->label('Categoria')->badge()->color('gray'),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (CashFlowType $state): string => $state->label())->color(fn (CashFlowType $state): string => $state->color()),
                TextColumn::make('order.number')->label('Pedido')->placeholder('—')->url(fn (CashFlowEntry $record): ?string => $record->order ? OrderResource::getUrl('view', ['record' => $record->order]) : null),
                TextColumn::make('amount_cents')->label('Valor')->alignEnd()->sortable()->formatStateUsing(fn (int $state, CashFlowEntry $record): string => ($record->type === CashFlowType::EXPENSE ? '− ' : '+ ').BrazilianMoney::display($state))->color(fn (CashFlowEntry $record): string => $record->type->color())->weight('semibold'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options([
                    CashFlowType::INCOME->value => 'Entradas',
                    CashFlowType::EXPENSE->value => 'Saídas',
                ]),
                SelectFilter::make('category')->label('Categoria')->options(fn (): array => CashFlowEntry::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category', 'category')->all()),
                Filter::make('this_month')->label('Este mês')->query(fn (Builder $query): Builder => $query->whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->visible(fn (CashFlowEntry $record): bool => ! $record->isAutomatic()),
                DeleteAction::make()->iconButton()->visible(fn (CashFlowEntry $record): bool => ! $record->isAutomatic())->using(function (CashFlowEntry $record): bool {
                    app(DeleteCashFlowEntryAction::class)->execute($record);

                    return true;
                }),
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof CashFlowEntry && ! $record->isAutomatic();
    }

    public static function canDelete(Model $record): bool
    {
        return $record instanceof CashFlowEntry && ! $record->isAutomatic();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashFlowEntries::route('/'),
            'create' => CreateCashFlowEntry::route('/create'),
            'edit' => EditCashFlowEntry::route('/{record}/edit'),
        ];
    }
}
