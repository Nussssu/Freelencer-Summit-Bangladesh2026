<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Enums\AwardStatus;
use App\Filament\Resources\Submissions\AwardNominationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * The jury's workflow as tabs: this edition's nominations by stage (evaluation, shortlist, winners), and the archive
 * of earlier editions. Publishing to the site is the "Show on the public shortlist / winners" toggles.
 */
class ListAwardNominations extends ListRecords
{
    protected static string $resource = AwardNominationResource::class;

    public function getTabs(): array
    {
        $edition = (string) config('summit.edition');
        $current = fn (Builder $query) => $query->where('edition', $edition);

        return [
            'all' => Tab::make("Edition {$edition}")->modifyQueryUsing($current),
            'evaluation' => Tab::make('In evaluation')->icon('heroicon-o-clipboard-document-check')
                ->modifyQueryUsing(fn (Builder $query) => $current($query)->whereIn('status', [AwardStatus::Submitted->value, AwardStatus::UnderReview->value])),
            'shortlist' => Tab::make('Shortlisted')->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $current($query)->where('status', AwardStatus::Shortlisted->value)),
            'winners' => Tab::make('Winners')->icon('heroicon-o-trophy')
                ->modifyQueryUsing(fn (Builder $query) => $current($query)->where('status', AwardStatus::Winner->value)),
            'archive' => Tab::make('Archive')->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('edition', '!=', $edition)),
        ];
    }
}
