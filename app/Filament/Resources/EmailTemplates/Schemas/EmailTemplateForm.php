<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Toggle::make('enabled')->label('Send this email')->helperText(fn (?EmailTemplate $record) => $record?->key === 'admin_notification'
                    ? 'Goes to the admin addresses in Settings › Email.'
                    : 'Goes to the person who submitted the form.'),
                TextInput::make('subject')->required()->maxLength(255),
                TextInput::make('heading')->required()->maxLength(255)->helperText('The large title at the top of the email.'),
                MarkdownEditor::make('body')->required()->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']])
                    ->helperText('Placeholders: '.implode(' ', EmailTemplate::PLACEHOLDERS).'. {fields} inserts a table of everything submitted (best for the admin email).'),
            ]),
        ]);
    }
}
