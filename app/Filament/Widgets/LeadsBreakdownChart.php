<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class LeadsBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Inquiries & leads';

    protected ?string $description = 'All submissions by form';

    protected ?string $maxHeight = '280px';

    /** Lead type => the name staff see in the sidebar. */
    private const LABELS = [
        'summit_registration' => 'Registrations', 'award_application' => 'Nominations',
        'exhibit_enquiry' => 'Exhibition inquiries', 'exhibition_application' => 'Applications',
    ];

    protected function getData(): array
    {
        $counts = Lead::query()->selectRaw('type, count(*) as total')->groupBy('type')->orderByDesc('total')->pluck('total', 'type');

        return [
            'datasets' => [[
                'data' => $counts->values()->all(),
                'backgroundColor' => array_slice(['#0f172a', '#087f8c', '#eab94b', '#3167ae', '#7fe0c8', '#f42a41', '#94a3b8', '#8db0e6'], 0, max(1, $counts->count())),
                'borderWidth' => 0,
            ]],
            'labels' => $counts->keys()->map(fn ($t) => self::LABELS[$t] ?? Str::headline($t))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['cutout' => '62%', 'plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
