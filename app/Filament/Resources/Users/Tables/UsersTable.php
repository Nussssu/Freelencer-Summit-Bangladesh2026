<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Throwable;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $status = fn (User $u) => $u->suspended_at ? 'Suspended' : ($u->last_login_at ? 'Active' : 'Invited');
        $canManage = fn (User $u) => UserResource::canEdit($u) && ! $u->is(auth()->user());

        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('roles.name')->label('Role')->badge(),
                TextColumn::make('status')->badge()->state($status)->color(fn (string $state) => ['Active' => 'success', 'Invited' => 'info', 'Suspended' => 'danger'][$state]),
                TextColumn::make('last_login_at')->label('Last login')->since()->sortable()->placeholder('Never'),
            ])
            ->defaultSort('name')
            ->filters([SelectFilter::make('roles')->relationship('roles', 'name')->label('Role')])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    Action::make('suspend')->icon('heroicon-o-no-symbol')->color('danger')->requiresConfirmation()
                        ->modalDescription('They are signed out on their next click and can\'t sign in until reactivated.')
                        ->visible(fn (User $u) => $canManage($u) && ! $u->suspended_at)
                        ->action(fn (User $u) => $u->forceFill(['suspended_at' => now()])->save()), // audit: "Updated User (suspended_at)"
                    Action::make('reactivate')->icon('heroicon-o-check-circle')->color('success')
                        ->visible(fn (User $u) => $canManage($u) && $u->suspended_at)
                        ->action(fn (User $u) => $u->forceFill(['suspended_at' => null])->save()),
                    Action::make('invite')->label('Send invite again')->icon('heroicon-o-envelope')
                        ->visible(fn (User $u) => $canManage($u) && ! $u->suspended_at)
                        ->action(function (User $u) {
                            try {
                                UserResource::invite($u);
                                Notification::make()->success()->title("Invitation sent to {$u->email}")->send();
                            } catch (Throwable $e) {
                                Notification::make()->danger()->title('Sending failed')->body($e->getMessage())->send();
                            }
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Same rules as the row delete: never yourself, and only a Super Admin removes a Super Admin.
                    BulkAction::make('delete')->label('Delete selected')->icon('heroicon-o-trash')->color('danger')
                        ->requiresConfirmation()->modalDescription('Your own account and accounts you may not remove are skipped.')
                        ->authorize(fn () => UserResource::can('deleteAny'))
                        ->action(function (Collection $records) {
                            [$allowed, $kept] = $records->partition(fn (User $u) => UserResource::canDelete($u));
                            $allowed->each->delete();
                            $note = Notification::make()->title("Deleted {$allowed->count()} user(s)");
                            $kept->isEmpty() ? $note->success()->send() : $note->warning()->body('Kept: '.$kept->pluck('email')->implode(', '))->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
