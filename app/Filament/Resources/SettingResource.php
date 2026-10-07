<?php

namespace App\Filament\Resources;

use App\Models\Entry;
use App\Models\Media;
use App\Models\Setting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    public static function form(Schema $s): Schema
    {
        return $s->components([TextInput::make('name')->required()->default('Life of Pathi'), TextInput::make('tagline')->default('Still growing.'), Textarea::make('introduction'), TextInput::make('contact_email')->email(), Textarea::make('availability'), KeyValue::make('social_links')->helperText('Label => HTTPS URL'), MarkdownEditor::make('now_content')->fileAttachments(false)->toolbarButtons(['bold', 'italic', 'heading', 'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo']), DatePicker::make('now_date'), Select::make('cv_media_id')->label('CV download')->options(fn () => Media::pluck('name', 'id')), Select::make('pathway_id')->label('Featured reading pathway')->options(fn () => Entry::where('type', 'project')->pluck('title', 'id'))]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([TextColumn::make('name')->searchable(), TextColumn::make('updated_at')->since()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageSettings::route('/')];
    }
}
