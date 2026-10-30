<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use UnitEnum;

/** CMS staff accounts. Invited by email (they set their own password); suspended accounts can't sign in. */
class UserResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string $module = 'users';

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Access';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    /** Only a Super Admin may change or remove a Super Admin; nobody deletes their own account. */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $me = auth()->user();
        if ($record instanceof User && (($record->isSuperAdmin() && ! $me?->isSuperAdmin()) || ($action === 'delete' && $record->is($me)))) {
            return Response::deny();
        }

        return static::modulePermission($action, $record);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    /** Email a link to the panel's password reset page, which doubles as "set your password". */
    public static function invite(User $user): void
    {
        $token = Password::broker(Filament::getAuthPasswordBroker())->createToken($user);
        $notification = app(ResetPassword::class, ['token' => $token]);
        $notification->url = Filament::getResetPasswordUrl($token, $user);
        $user->notify($notification);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
