<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminGroups\Pages;

use App\Filament\Resources\AdminGroups\AdminGroupResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAdminGroup extends CreateRecord
{
    protected static string $resource = AdminGroupResource::class;
}
