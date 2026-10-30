<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

/** Three columns: stat cards across the top, then the trend chart (2/3) beside the leads breakdown (1/3). */
class Dashboard extends BaseDashboard
{
    protected static string|UnitEnum|null $navigationGroup = 'Overview';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 3];
    }
}
