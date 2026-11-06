<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\Messages\Pages\ListMessageLogs;
use App\Filament\Support\CsvExport;
use App\Models\MessageLog;
use App\Support\Statuses;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Every email (and SMS, once a gateway is connected) the site sent or failed to send: read-only. */
class MessageLogResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string $module = 'messages';

    protected static ?string $model = MessageLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Messages';

    protected static ?string $navigationLabel = 'Email & SMS log';

    protected static ?string $pluralModelLabel = 'Email & SMS log';

    protected static ?string $modelLabel = 'message';

    protected static ?int $navigationSort = 2;

    /** A log is a record of what happened: it is never created or edited by hand. */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $action = $action instanceof UnitEnum ? $action->name : $action;

        return in_array($action, ['viewAny', 'view'], true) ? static::modulePermission($action, $record) : Response::deny();
    }

    public static function getNavigationBadge(): ?string
    {
        $n = MessageLog::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Sent')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('channel')->badge()->formatStateUsing(fn (string $state) => strtoupper($state) === 'SMS' ? 'SMS' : 'Email')->color(fn (string $state) => $state === 'sms' ? 'warning' : 'primary'),
                TextColumn::make('recipient')->searchable()->copyable(),
                TextColumn::make('subject')->searchable()->wrap()->limit(60)->placeholder('—'),
                TextColumn::make('lead.reference')->label('Submission')->fontFamily('mono')->placeholder('—')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => Statuses::color($state))->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('error')->limit(60)->tooltip(fn ($state) => $state)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('channel')->options(['email' => 'Email', 'sms' => 'SMS']),
                SelectFilter::make('status')->options(['sent' => 'Sent', 'failed' => 'Failed']),
            ])
            ->headerActions([CsvExport::action('Message log', ['Sent' => 'created_at', 'Channel' => 'channel', 'Recipient' => 'recipient', 'Subject' => 'subject', 'Submission' => 'lead.reference', 'Status' => 'status', 'Error' => 'error'])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListMessageLogs::route('/')];
    }
}
