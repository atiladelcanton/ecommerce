<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminUsers;

use App\Domain\Access\AdminPermissions;
use App\Models\User;
use App\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Filament\Resources\AdminUsers\Pages\ListAdminUsers;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class AdminUserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'usuário administrativo';
    protected static ?string $pluralModelLabel = 'Usuários administrativos';
    protected static string|\UnitEnum|null $navigationGroup = 'Acessos';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool { return AdminPermissions::check(AdminPermissions::ACCESS); }
    public static function canCreate(): bool { return self::canViewAny(); }
    public static function canView(Model $record): bool { return self::canViewAny(); }
    public static function canEdit(Model $record): bool { return self::canViewAny(); }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Conta do painel')->schema([
                TextInput::make('name')->label('Nome')->required()->maxLength(255),
                TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                Select::make('admin_group_id')->label('Grupo de acesso')->relationship('adminGroup', 'name')
                    ->searchable()->preload()->required()->helperText('Administrador: acesso completo. Operacional: todos os módulos, exceto configurações e acessos.'),
                TextInput::make('password')->label('Senha')->password()->revealable()->minLength(10)
                    ->formatStateUsing(fn (): ?string => null)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Ao editar, deixe em branco para manter a senha atual.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('email')->label('E-mail')->searchable(),
            TextColumn::make('adminGroup.name')->label('Grupo')->badge()->placeholder('Sem acesso'),
            TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y'),
        ])->recordActions([EditAction::make()])->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminUsers::route('/'),
            'create' => CreateAdminUser::route('/create'),
            'edit' => EditAdminUser::route('/{record}/edit'),
        ];
    }
}
