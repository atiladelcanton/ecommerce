<?php

declare(strict_types=1);

namespace App\Filament\Resources\InfluencerCommissions;

use App\Domain\Access\AdminPermissions;
use App\Domain\Influencer\Models\InfluencerCommission;
use App\Domain\Influencer\Services\ManageInfluencerCommission;
use App\Filament\Resources\InfluencerCommissions\Pages\ListInfluencerCommissions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class InfluencerCommissionResource extends Resource
{
    protected static ?string $model = InfluencerCommission::class;
    protected static ?string $modelLabel = 'comissão de influenciador';
    protected static ?string $pluralModelLabel = 'Comissões de influenciadores';
    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool { return AdminPermissions::check(AdminPermissions::SALES_INFLUENCERS); }
    public static function canView(Model $record): bool { return self::canViewAny(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('influencer.name')->label('Influenciador')->searchable(),
            TextColumn::make('order.number')->label('Pedido')->searchable(),
            TextColumn::make('coupon.code')->label('Cupom')->placeholder('—'),
            TextColumn::make('base_cents')->label('Base (sem frete)')->formatStateUsing(fn ($state): string => 'R$ '.number_format((int) $state / 100, 2, ',', '.')),
            TextColumn::make('commission_bps')->label('Taxa')->formatStateUsing(fn ($state): string => number_format((int) $state / 100, 2, ',', '.').'%'),
            TextColumn::make('amount_cents')->label('Comissão')->formatStateUsing(fn ($state): string => 'R$ '.number_format((int) $state / 100, 2, ',', '.')),
            TextColumn::make('status')->label('Situação')->badge()->formatStateUsing(fn ($state) => InfluencerCommission::statuses()[$state] ?? $state)
                ->color(fn ($state) => match ($state) { 'paid' => 'success', 'approved' => 'info', 'review' => 'danger', 'cancelled', 'reversed' => 'gray', default => 'warning' }),
        ])->defaultSort('created_at', 'desc')->recordActions([
            Action::make('approve')->label('Aprovar')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (InfluencerCommission $record): bool => $record->status === 'forecast' && auth('web')->user()?->adminGroup?->slug === 'administrador')
                ->requiresConfirmation()
                ->action(fn (InfluencerCommission $record) => app(ManageInfluencerCommission::class)->advance($record, 'approved', (int) auth('web')->id())),
            Action::make('paid')->label('Registrar repasse')->color('primary')->icon('heroicon-o-banknotes')
                ->visible(fn (InfluencerCommission $record): bool => $record->status === 'approved' && auth('web')->user()?->adminGroup?->slug === 'administrador')
                ->schema([Textarea::make('reference')->label('Referência do pagamento Pix ou comprovante')->required()->minLength(5)])
                ->requiresConfirmation()->modalDescription('Registre somente depois de realizar a transferência Pix fora do sistema.')
                ->action(fn (InfluencerCommission $record, array $data) => app(ManageInfluencerCommission::class)->advance($record, 'paid', (int) auth('web')->id(), $data['reference'])),
            Action::make('reverse')->label('Reverter')->color('danger')->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (InfluencerCommission $record): bool => in_array($record->status, ['review', 'forecast', 'approved'], true) && auth('web')->user()?->adminGroup?->slug === 'administrador')
                ->schema([Textarea::make('note')->label('Justificativa da reversão')->required()->minLength(10)])
                ->requiresConfirmation()
                ->action(fn (InfluencerCommission $record, array $data) => app(ManageInfluencerCommission::class)->advance($record, 'reversed', (int) auth('web')->id(), $data['note'])),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListInfluencerCommissions::route('/')];
    }
}
