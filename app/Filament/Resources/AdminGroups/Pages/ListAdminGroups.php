<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminGroups\Pages;

use App\Filament\Resources\AdminGroups\AdminGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListAdminGroups extends ListRecords
{
    protected static string $resource = AdminGroupResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo grupo')]; }
}
