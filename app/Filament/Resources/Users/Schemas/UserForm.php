<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Access;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
                Select::make('role')->required()->native(false)->dehydrated(false)
                    // Only a Super Admin can hand out Super Admin.
                    ->options(fn () => Role::query()->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('name', '!=', Access::SUPER_ADMIN))->orderBy('id')->pluck('name', 'name'))
                    ->afterStateHydrated(fn (Select $component, ?User $record) => $component->state($record?->roles->first()?->name))
                    ->saveRelationshipsUsing(fn (User $record, string $state) => $record->syncRoles([$state]))
                    ->helperText('Super Admin can do everything. Edit what the other roles can do in Access › Roles.'),
            ]),
        ]);
    }
}
