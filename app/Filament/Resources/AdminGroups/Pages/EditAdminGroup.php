<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminGroups\Pages;

use App\Filament\Resources\AdminGroups\AdminGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditAdminGroup extends EditRecord
{
    protected static string $resource = AdminGroupResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
