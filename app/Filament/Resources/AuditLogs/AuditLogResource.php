<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Read-only: rows are written by AuditLog::record and never edited or deleted from the CMS. */
class AuditLogResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string $module = 'audit_logs';

    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Access';

    protected static ?string $navigationLabel = 'Audit logs';

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return in_array($action, ['view', 'viewAny'], true) ? static::modulePermission($action, $record) : Response::deny();
    }

    public static function table(Table $table): Table
    {
        $colors = ['Created' => 'success', 'Updated' => 'info', 'Deleted' => 'danger', 'Logged in' => 'gray', 'Logged out' => 'gray'];

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Time')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('user.name')->label('User')->placeholder('System')->searchable(),
                TextColumn::make('action')->badge()->color(fn (string $state) => $colors[$state] ?? 'warning'),
                TextColumn::make('module')->searchable(),
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('ip_address')->label('IP address')->fontFamily('mono')->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')->options(fn () => AuditLog::query()->distinct()->orderBy('action')->pluck('action', 'action')->all()),
                SelectFilter::make('module')->options(fn () => AuditLog::query()->distinct()->orderBy('module')->pluck('module', 'module')->all()),
                SelectFilter::make('user')->relationship('user', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAuditLogs::route('/')];
    }
}
