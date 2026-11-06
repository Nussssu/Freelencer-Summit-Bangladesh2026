<?php

namespace App\Support;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * A new public submission, in the admin panel's notification bell, for every team member who may see it (the
 * submission's module view permission; Super Admins always). Emails to the inbox are Lead::notify().
 */
class NotifyTeamOfSubmission
{
    /** Lead type (snake model name) => module permission. */
    private const MODULES = [
        'summit_registration' => 'registrations', 'program_registration' => 'registrations',
        'forum_application' => 'memberships', 'ambassador_lead' => 'memberships',
        'award_application' => 'nominations',
        'exhibition_application' => 'exhibitions', 'exhibit_enquiry' => 'exhibitions',
        'sponsorship_enquiry' => 'sponsorships',
        'contact_message' => 'messages',
    ];

    public static function send(Lead $lead): void
    {
        $module = self::MODULES[$lead->type] ?? 'leads';
        $permission = Access::permission('view', $module);
        $users = User::query()->get()->filter(fn (User $u) => $u->isSuperAdmin() || $u->can($permission));
        if ($users->isEmpty()) {
            return;
        }

        Notification::make()
            ->title('New '.Str::lower($lead->typeLabel()))
            ->body(collect([$lead->name, $lead->reference])->filter()->implode(' · '))
            ->icon('heroicon-o-inbox-arrow-down')
            ->info()
            ->actions([Action::make('open')->label('Open')->url(LeadResource::getUrl('edit', ['record' => $lead]))->markAsRead()])
            ->sendToDatabase($users);
    }
}
