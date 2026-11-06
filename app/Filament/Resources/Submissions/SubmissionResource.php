<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Support\CsvExport;
use App\Filament\Support\SubmissionCsvImport;
use App\Support\Statuses;
use BackedEnum;
use App\Mail\LeadMail;
use App\Models\AuditLog;
use App\Models\EmailTemplate;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use App\Models\District;
use App\Rules\BdMobile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

/**
 * One public form's submissions: view, correct or delete (soft delete) in modals; never created from the admin.
 * Each submission moves through its workflow statuses (static::statuses()) with internal notes, and the table exports
 * to CSV as filtered or as selected. Encrypted columns (phones) are decrypted by the model casts for display and
 * editing only; they are never searchable or exported. Each form's own resource sets the columns, view and edit form.
 */
abstract class SubmissionResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string|UnitEnum|null $navigationGroup = 'Leads & CRM';

    /** Submissions only come from the public forms. */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return in_array($action, ['create', 'replicate'], true) ? Response::deny() : static::modulePermission($action, $record);
    }

    /** Global search hits open the table filtered to the record (these resources have no record pages). */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['search' => $record->getAttribute(static::$recordTitleAttribute)]);
    }

    /** @return list<Column> */
    abstract protected static function columns(): array;

    /** Workflow statuses (value => label), or null for a form without one. */
    protected static function statuses(): ?array
    {
        return null;
    }

    /** CSV columns (label => attribute or fn (Model) => value); phones are deliberately left out. */
    protected static function exportColumns(): array
    {
        return ['Reference' => 'reference', 'Received' => 'created_at'];
    }

    /** Submissions with a status still to act on, for the sidebar badge. */
    public static function getNavigationBadge(): ?string
    {
        $first = array_key_first(static::statuses() ?? []);
        if (! $first) {
            return null;
        }
        $n = static::getModel()::query()->where('status', $first)->count();

        return $n ? (string) $n : null;
    }

    public static function table(Table $table): Table
    {
        $statuses = static::statuses();
        $export = [...static::exportColumns(), ...($statuses ? ['Status' => fn (Model $m) => $statuses[self::statusValue($m)] ?? self::statusValue($m), 'Notes' => 'notes'] : [])];
        $registrations = static::$navigationGroup === 'Registrations';
        if ($registrations || static::$inlineFilters) {
            // Registrations: the filters sit open in the search row (styled by .fs-inline-filters in the admin theme)
            // and apply as soon as they change; the column chooser is a labelled button, so nothing hides behind an icon.
            $table->filtersLayout(FiltersLayout::AboveContent)->deferFilters(false)
                ->filtersFormColumns(['sm' => 2, 'lg' => 3, 'xl' => 6])
                ->extraAttributes(['class' => 'fs-inline-filters'])
                ->columnManagerTriggerAction(fn (Action $action) => $action->button()->label('Columns'));
        }

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('d M Y, h:i A')->sortable(),
                ...static::columns(),
                ...($statuses ? [static::statusColumn()] : []),
            ])
            ->filters($statuses ? [static::statusFilter()] : [])
            ->defaultSort('created_at', 'desc')
            ->headerActions([CsvExport::action(static::getPluralModelLabel(), $export), ...(static::importColumns() ? [SubmissionCsvImport::action(static::class)] : [])])
            ->recordActions([
                ...(static::$approvalTemplate ? static::reviewActions(inModal: false) : []),
                ViewAction::make()->iconButton()->tooltip('View')->extraModalFooterActions(fn (Model $record) => [
                    ...(static::$approvalTemplate ? static::reviewActions(inModal: true) : []),
                    ...($registrations && filled($record->getAttribute('email')) ? [static::replyAction($record)] : []),
                ]),
                EditAction::make()->iconButton()->tooltip('Edit'), DeleteAction::make()->iconButton()->tooltip('Delete'),
            ]) // icons keep actions on screen at desktop width
            ->toolbarActions([BulkActionGroup::make([
                ...($statuses ? [static::setStatusBulk($statuses)] : []),
                CsvExport::bulk(static::getPluralModelLabel(), $export),
                DeleteBulkAction::make(), // soft delete; needs the module's delete permission
            ])]);
    }

    /**
     * Email template key sent to the applicant when approved (Messages › Email templates). Set on application forms
     * (statuses pending / approved / rejected) to get one-click Approve and Reject; null = no review buttons.
     */
    protected static ?string $approvalTemplate = null;

    /** Approve (emails the applicant) and Reject, as row icons or as view-window buttons. Hidden once in that status. */
    protected static function reviewActions(bool $inModal): array
    {
        $style = fn (Action $a, string $tip) => $inModal ? $a->cancelParentActions() : $a->iconButton()->tooltip($tip);

        return [
            $style(Action::make('approve')->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (Model $record) => static::can('update', $record) && self::statusValue($record) !== 'approved')
                ->requiresConfirmation()->modalHeading(fn (Model $record) => 'Approve '.$record->getAttribute('name').'?')
                ->modalDescription('Their status becomes Approved and they get the approval email (editable in Messages › Email templates).')
                ->modalSubmitActionLabel('Approve')->modalIcon('heroicon-o-check-circle')
                ->action(fn (Model $record) => static::approve($record)), 'Approve'),
            $style(Action::make('reject')->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (Model $record) => static::can('update', $record) && self::statusValue($record) !== 'rejected')
                ->requiresConfirmation()->modalHeading(fn (Model $record) => 'Reject '.$record->getAttribute('name').'?')
                ->modalDescription('Their status becomes Rejected. No email is sent.')
                ->modalSubmitActionLabel('Reject')->modalIcon('heroicon-o-x-circle')
                ->action(function (Model $record) {
                    $record->update(['status' => 'rejected']);
                    AuditLog::record('Rejected', static::getPluralModelLabel(), 'Rejected '.$record->getAttribute('reference'), $record);
                    Notification::make()->success()->title('Rejected')->send();
                }), 'Reject'),
        ];
    }

    /** Sets Approved and emails the applicant the approval template (sent now, so it never waits on the queue). */
    public static function approve(Model $record): void
    {
        $record->update(['status' => 'approved']);
        AuditLog::record('Approved', static::getPluralModelLabel(), 'Approved '.$record->getAttribute('reference'), $record);

        $template = EmailTemplate::query()->where('key', static::$approvalTemplate)->where('enabled', true)->first();
        $email = $record->getAttribute('email');
        if (! $template || ! $email) {
            Notification::make()->success()->title('Approved')->body($template ? null : 'The approval email is switched off in Email templates, so none was sent.')->send();

            return;
        }
        $lead = Lead::query()->where('submittable_type', $record->getMorphClass())->where('submittable_id', $record->getKey())->first()
            ?? new Lead(['type' => Str::snake(class_basename($record)), 'reference' => $record->getAttribute('reference'), 'name' => $record->getAttribute('name'), 'email' => $email,
                'data' => array_intersect_key($record->attributesToArray(), array_flip(['name', 'email', 'phone', 'reference']))]);
        try {
            Mail::to($email)->sendNow(new LeadMail($lead, $template));
            Notification::make()->success()->title('Approved and emailed')->body("The approval email was sent to {$email}.")->send();
        } catch (Throwable $e) {
            Notification::make()->warning()->title('Approved, but the email was not sent')->body($e->getMessage())->persistent()->send();
        }
    }

    /** Reply by email from the view modal: opens the mail app addressed to the person, subject filled in. */
    protected static function replyAction(Model $record): Action
    {
        $subject = 'Re: '.static::getModelLabel().($record->getAttribute('reference') ? ' ('.$record->getAttribute('reference').')' : '').' · '.config('summit.name');

        return Action::make('reply')->label('Reply')->icon('heroicon-o-arrow-uturn-left')->color('primary')
            ->url('mailto:'.$record->getAttribute('email').'?subject='.rawurlencode($subject));
    }

    /** Inline filter row, themed dropdowns and date range like the Registrations pages, on a form outside that group. */
    protected static bool $inlineFilters = false;

    /** Import CSV: the person's name / email / phone columns (header => attribute or fn), read before importColumns(). */
    public static function importIdentity(): array
    {
        return ['Name' => 'name', 'Email' => 'email', 'Phone' => fn (string $v) => ['phone' => BdMobile::normalize($v)]];
    }

    /** Import CSV: attributes a row must have (else skipped); the first one ending in "email" is checked as an email. */
    public static function importRequired(): array
    {
        return ['name', 'email', 'phone'];
    }

    /** Import CSV: true when this row is already in the list (beyond the same reference, which is always skipped). */
    public static function importDuplicate(array $attributes): bool
    {
        return false;
    }

    /**
     * Import CSV columns (header label => attribute, or fn (string $cell): array of attributes). Empty: no Import
     * button. Reference, Received, Name, Email, Phone, Status and Notes are read for every form that has columns here.
     *
     * @return array<string, string|\Closure>
     */
    public static function importColumns(): array
    {
        return [];
    }

    /** Attributes an imported row always gets (consent is recorded as "import"). */
    public static function importDefaults(): array
    {
        return [];
    }

    /** District with an "All districts" choice at the top (the default), in the themed dropdown. */
    protected static function districtFilter(): SelectFilter
    {
        return SelectFilter::make('district')->label('District')
            ->options(fn () => ['all' => 'All districts'] + District::query()->orderBy('name')->pluck('name', 'id')->all())
            ->default('all')->searchable()->native(false)
            ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) && $data['value'] !== 'all' ? $query->where('district_id', $data['value']) : $query)
            ->indicateUsing(fn (array $data) => filled($data['value'] ?? null) && $data['value'] !== 'all' ? 'District: '.District::query()->whereKey($data['value'])->value('name') : null);
    }

    /** Received from / to (dates in the summit timezone). */
    protected static function receivedFilter(): Filter
    {
        return Filter::make('received')
            ->schema([
                // Native date boxes (mm/dd/yyyy + calendar icon) with FROM / TO between them, set in the admin theme.
                DatePicker::make('from')->label('From')->hiddenLabel()->extraFieldWrapperAttributes(['class' => 'fs-date-from']),
                DatePicker::make('until')->label('To')->hiddenLabel()->extraFieldWrapperAttributes(['class' => 'fs-date-to']),
            ])
            ->columns(2)->columnSpan(2)
            ->query(fn (Builder $query, array $data) => $query
                ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
                ->when($data['until'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d)))
            ->indicateUsing(fn (array $data) => array_values(array_filter([
                ($data['from'] ?? null) ? 'From '.Carbon::parse($data['from'])->format('d M Y') : null,
                ($data['until'] ?? null) ? 'To '.Carbon::parse($data['until'])->format('d M Y') : null,
            ])));
    }

    protected static function statusColumn(): TextColumn
    {
        return TextColumn::make('status')->badge()->sortable()
            ->formatStateUsing(fn ($state) => static::statuses()[self::statusValue($state)] ?? self::statusValue($state))
            ->color(fn ($state) => Statuses::color(self::statusValue($state)));
    }

    protected static function statusFilter(): SelectFilter
    {
        // Registrations use the themed dropdown (site teal highlight, "All" selectable); other forms keep the native one.
        return SelectFilter::make('status')->options(static::statuses() ?? [])
            ->when(static::$navigationGroup === 'Registrations' || static::$inlineFilters, fn (SelectFilter $f) => $f->native(false)->selectablePlaceholder());
    }

    protected static function setStatusBulk(array $statuses): BulkAction
    {
        return BulkAction::make('setStatus')->label('Set status')->icon('heroicon-o-tag')
            ->visible(fn () => static::can('update'))
            ->schema([Select::make('status')->options($statuses)->required()->native(false)])
            ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
            ->deselectRecordsAfterCompletion();
    }

    /** Status and internal notes, for the edit form. */
    protected static function workflowSection(): Section
    {
        return Section::make('Workflow')->columns(2)->columnSpanFull()->schema([
            Select::make('status')->options(static::statuses() ?? [])->required()->native(false),
            Textarea::make('notes')->label('Internal notes')->rows(3)->helperText('Only the team sees these. Never emailed.'),
        ]);
    }

    /** Status and notes, for the view modal. */
    protected static function workflowEntries(): Section
    {
        return Section::make('Workflow')->columns(2)->columnSpanFull()->schema([
            TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => static::statuses()[self::statusValue($state)] ?? self::statusValue($state))->color(fn ($state) => Statuses::color(self::statusValue($state))),
            TextEntry::make('notes')->label('Internal notes')->placeholder('—'),
        ]);
    }

    private static function statusValue(mixed $v): ?string
    {
        $v = $v instanceof Model ? $v->getAttribute('status') : $v;

        return $v instanceof BackedEnum ? (string) $v->value : $v;
    }

    /** List-of-links JSON ([{platform, url}] or [url]) as plain URLs. */
    public static function links(mixed $links): array
    {
        return collect((array) $links)->map(fn ($l) => is_array($l) ? ($l['url'] ?? implode(' ', array_filter($l, 'is_string'))) : (string) $l)->filter()->values()->all();
    }
}
