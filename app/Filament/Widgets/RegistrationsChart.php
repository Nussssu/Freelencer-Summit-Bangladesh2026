<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\SummitRegistration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RegistrationsChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 2];

    protected ?string $heading = 'Registrations & leads';

    protected ?string $description = 'New submissions per day';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days'];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $from = today()->subDays($days - 1);
        $range = collect(range(0, $days - 1))->map(fn ($i) => $from->copy()->addDays($i)->toDateString());
        // Grouped in PHP (not SQL date functions) so it reads the same on MySQL and the SQLite test database.
        $perDay = fn ($query) => $query->where('created_at', '>=', $from)->pluck('created_at')
            ->countBy(fn (Carbon $at) => $at->toDateString());
        $registrations = $perDay(SummitRegistration::query());
        $leads = $perDay(Lead::query());

        return [
            'datasets' => [
                ['label' => 'Registrations', 'data' => $range->map(fn ($d) => $registrations[$d] ?? 0)->all(),
                    'borderColor' => '#0f172a', 'backgroundColor' => 'rgba(15, 23, 42, .08)', 'fill' => true, 'tension' => .35, 'pointRadius' => 0],
                ['label' => 'All leads', 'data' => $range->map(fn ($d) => $leads[$d] ?? 0)->all(),
                    'borderColor' => '#087f8c', 'backgroundColor' => 'transparent', 'tension' => .35, 'pointRadius' => 0],
            ],
            'labels' => $range->map(fn ($d) => Carbon::parse($d)->format('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'top', 'align' => 'end']],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8, 'maxRotation' => 0]]],
            'interaction' => ['mode' => 'index', 'intersect' => false],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
