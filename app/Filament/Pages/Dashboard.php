<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

final class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Painel de Controle';

    protected static string|UnitEnum|null $navigationGroup = 'Painel';
}
