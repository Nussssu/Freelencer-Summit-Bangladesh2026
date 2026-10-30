<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/** Three columns: stat cards across the top, then the trend chart (2/3) beside the leads breakdown (1/3). */
class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 3];
    }
}
