<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Support\Access;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UnitEnum;

/** What each role may do, per module and action. Super Admin always has every permission and can't be edited. */
class RoleResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string $module = 'roles';

    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Access Control';

    protected static ?string $navigationLabel = 'Roles & permissions';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        if ($record instanceof Role && $record->name === Access::SUPER_ADMIN && $action !== 'view') {
            return Response::deny();
        }
        if ($action === 'delete' && $record instanceof Role && $record->users()->exists()) {
            return Response::deny('Move its users to another role first.');
        }

        return static::modulePermission($action, $record);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(60)->unique(ignoreRecord: true),
                CheckboxList::make('permissions')->relationship('permissions', 'name', fn ($query) => $query->orderBy('id')) // Access::sync creates them module by module
                    ->getOptionLabelFromRecordUsing(function (Permission $p) {
                        [$verb, $module] = explode(' ', $p->name, 2);

                        return (Access::MODULES[$module] ?? $module).' · '.ucfirst($verb);
                    })
                    ->columns(4)->gridDirection('row')->bulkToggleable()->searchable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->weight('medium'),
                TextColumn::make('permissions_count')->counts('permissions')->label('Permissions')
                    ->formatStateUsing(fn ($state, Role $r) => $r->name === Access::SUPER_ADMIN ? 'All' : $state),
                TextColumn::make('users_count')->counts('users')->label('Users'),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
