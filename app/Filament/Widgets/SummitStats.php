<?php

namespace App\Filament\Widgets;

use App\Models\AwardApplication;
use App\Models\ExhibitEnquiry;
use App\Models\ExhibitionApplication;
use App\Models\Lead;
use App\Models\SummitRegistration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SummitStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $week = now()->subWeek();

        return [
            Stat::make('Total registrations', number_format(SummitRegistration::count()))
                ->description(SummitRegistration::where('created_at', '>=', $week)->count().' in the last 7 days')->icon('heroicon-o-ticket'),
            Stat::make('Pending inquiries', number_format(Lead::where('status', 'new')->count()))
                ->description('New leads not yet contacted')->color('warning')->icon('heroicon-o-inbox')
                ->url(route('filament.admin.resources.leads.index', ['filters' => ['status' => ['value' => 'new']]])),
            Stat::make('Award nominations', number_format(AwardApplication::count()))->icon('heroicon-o-trophy'),
            Stat::make('Exhibition inquiries', number_format(ExhibitEnquiry::count() + ExhibitionApplication::count()))->icon('heroicon-o-building-storefront'),
        ];
    }
}
