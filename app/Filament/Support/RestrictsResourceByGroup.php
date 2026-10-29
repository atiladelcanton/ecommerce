<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Domain\Access\AdminPermissions;
use Illuminate\Database\Eloquent\Model;

/** Protege menu, listagens, edição e operações diretas dos Resources Filament. */
trait RestrictsResourceByGroup
{
    abstract protected static function permissionKey(): string;

    public static function canViewAny(): bool
    {
        return AdminPermissions::check(static::permissionKey());
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function canReplicate(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canReorder(): bool
    {
        return static::canViewAny();
    }

    public static function canRestore(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canRestoreAny(): bool
    {
        return static::canViewAny();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canForceDeleteAny(): bool
    {
        return static::canViewAny();
    }
}
