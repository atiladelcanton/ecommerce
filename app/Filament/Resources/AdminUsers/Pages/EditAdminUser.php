<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Domain\Access\AdminPermissions;
use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $newGroupId = (int) ($data['admin_group_id'] ?? 0);
        $oldIsAdmin = $record instanceof User && $record->adminGroup?->slug === 'administrador';
        $newIsAdmin = \App\Domain\Access\Models\AdminGroup::query()->whereKey($newGroupId)->where('slug', 'administrador')->exists();
        if ($oldIsAdmin && ! $newIsAdmin) {
            if ($record->is(auth('web')->user())) {
                throw ValidationException::withMessages(['data.admin_group_id' => 'Não é possível retirar seu próprio acesso de administrador.']);
            }
            $adminGroupId = $record->admin_group_id;
            $count = User::query()->where('admin_group_id', $adminGroupId)->count();
            if ($count <= 1) {
                throw ValidationException::withMessages(['data.admin_group_id' => 'A loja precisa manter pelo menos um administrador.']);
            }
        }
        $record->update($data);
        return $record;
    }
}
