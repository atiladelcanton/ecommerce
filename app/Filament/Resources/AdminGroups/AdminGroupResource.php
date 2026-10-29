<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminGroups;

use App\Domain\Access\AdminGroupAccessSummary;
use App\Domain\Access\AdminPermissions;
use App\Domain\Access\Models\AdminGroup;
use App\Filament\Resources\AdminGroups\Pages\CreateAdminGroup;
use App\Filament\Resources\AdminGroups\Pages\EditAdminGroup;
use App\Filament\Resources\AdminGroups\Pages\ListAdminGroups;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

final class AdminGroupResource extends Resource
{
    protected static ?string $model = AdminGroup::class;
    protected static ?string $modelLabel = 'grupo';
    protected static ?string $pluralModelLabel = 'Grupos de acesso';
    protected static string|\UnitEnum|null $navigationGroup = 'Acessos';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool { return AdminPermissions::check(AdminPermissions::ACCESS); }
    public static function canCreate(): bool { return self::canViewAny(); }
    public static function canView(Model $record): bool { return self::canViewAny(); }
    public static function canEdit(Model $record): bool
    {
        return self::canViewAny() && ! in_array($record->slug, ['administrador', 'operacional'], true);
    }
    public static function canDelete(Model $record): bool
    {
        return self::canEdit($record) && ! $record->users()->exists();
    }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Grupo')->description('Administrador e Operacional são grupos padrão protegidos. Crie outros grupos para definir acessos específicos.')->schema([
                TextInput::make('name')->label('Nome do grupo')->required()->maxLength(100),
                TextInput::make('slug')->label('Identificador')->required()->maxLength(100)->alphaDash()->unique(ignoreRecord: true)
                    ->helperText('Ex.: marketing. Não altere depois de criar o grupo.'),
            ]),
            Section::make('Permissões por módulo')->description('Marque apenas os módulos que este grupo pode acessar e operar. Sem marcação, o acesso é negado.')->schema([
                CheckboxList::make('permissions')->label('Módulos liberados')->options(AdminPermissions::labels())
                    ->columns(['default' => 1, 'md' => 2])->bulkToggleable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Grupo')
                ->searchable()
                ->sortable()
                ->weight('semibold')
                ->description(fn (AdminGroup $record): string => AdminGroupAccessSummary::description($record)),
            TextColumn::make('slug')
                ->label('Tipo')
                ->badge()
                ->formatStateUsing(fn (string $state, AdminGroup $record): string => AdminGroupAccessSummary::type($record))
                ->color(fn (AdminGroup $record): string => $record->slug === 'administrador' ? 'success' : ($record->slug === 'operacional' ? 'info' : 'gray'))
                ->icon(fn (AdminGroup $record): string => in_array($record->slug, ['administrador', 'operacional'], true) ? 'heroicon-m-lock-closed' : 'heroicon-m-user-group'),
            TextColumn::make('users_count')
                ->label('Usuários')
                ->counts('users')
                ->numeric()
                ->sortable()
                ->alignCenter(),
            TextColumn::make('access_summary')
                ->label('Acessos')
                ->state(fn (AdminGroup $record): string => AdminGroupAccessSummary::headline($record))
                ->badge()
                ->color(fn (AdminGroup $record): string => $record->slug === 'administrador' ? 'success' : ($record->slug === 'operacional' ? 'info' : 'gray'))
                ->description(fn (AdminGroup $record): string => AdminGroupAccessSummary::detail($record)),
        ])
            ->defaultSort('name')
            ->recordActions([
                Action::make('viewPermissions')
                    ->label('Ver acessos')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (AdminGroup $record): string => 'Permissões — ' . $record->name)
                    ->modalDescription('Confira quais módulos este grupo pode acessar. Esta consulta não altera os acessos.')
                    ->modalContent(fn (AdminGroup $record): View => view('filament.resources.admin-groups.access-modal', [
                        'group' => $record,
                        'summary' => AdminGroupAccessSummary::detail($record),
                        'sections' => AdminGroupAccessSummary::groupedPermissions($record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalWidth('2xl'),
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (AdminGroup $record): bool => self::canEdit($record)),
            ])
            ->recordUrl(fn (AdminGroup $record) => self::canEdit($record) ? self::getUrl('edit', ['record' => $record]) : null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminGroups::route('/'),
            'create' => CreateAdminGroup::route('/create'),
            'edit' => EditAdminGroup::route('/{record}/edit'),
        ];
    }
}
