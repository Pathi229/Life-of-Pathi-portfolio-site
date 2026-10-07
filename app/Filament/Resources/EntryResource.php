<?php

namespace App\Filament\Resources;

use App\Models\Entry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\URL;

abstract class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $kind = null;

    public static function getModelLabel(): string
    {
        return static::$kind ?? 'entry';
    }

    public static function getPluralModelLabel(): string
    {
        return match (static::$kind) {
            'project' => 'Projects','article' => 'Articles','video' => 'Videos',default => 'Entries'
        };
    }

    public static function getEloquentQuery(): Builder
    {
        $q = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        return static::$kind ? $q->where('type', static::$kind) : $q;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->schema([
                Select::make('type')->options(['project' => 'Project', 'article' => 'Article', 'video' => 'Video'])->default(static::$kind ?? 'article')->disabled((bool) static::$kind)->dehydrated()->required(),
                TextInput::make('title')->required()->maxLength(180), TextInput::make('slug')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)->maxLength(180),
                Textarea::make('summary')->maxLength(1000), MarkdownEditor::make('body')->fileAttachments(false)->toolbarButtons(['bold', 'italic', 'heading', 'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo'])->label('Story / guide (Markdown)')->columnSpanFull()->helperText('Headings, lists, callouts and links. HTML is stripped. Reusable images and downloads belong in Media below.'),
                Select::make('category')->options(['technology' => 'Technology', 'creative' => 'Creative / hospitality', 'tutorial' => 'Tutorial', 'story' => 'Story', 'reflection' => 'Reflection', 'update' => 'Project update']),
                Select::make('difficulty')->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']), TextInput::make('technique')->maxLength(100), TextInput::make('role')->label('My contribution / role')->maxLength(255), DatePicker::make('started_at'), DatePicker::make('finished_at'),
            ])->columns(2),
            Section::make('Relationships & reading order')->schema([
                Select::make('primary_branch_id')->relationship('primaryBranch', 'name')->searchable()->preload(), Select::make('branches')->relationship('branches', 'name')->multiple()->preload(), Select::make('channels')->relationship('channels', 'name')->multiple()->preload(), Select::make('project_id')->label('Project')->options(fn () => Entry::where('type', 'project')->pluck('title', 'id'))->searchable(), Select::make('series_id')->relationship('series', 'name')->preload(), TextInput::make('sequence')->numeric()->default(0)->helperText('One order shared by project and series navigation.'), Select::make('article_id')->options(fn () => Entry::where('type', 'article')->pluck('title', 'id'))->label('Related article (video)'), Select::make('media')->relationship('media', 'name')->multiple()->reorderable()->saveRelationshipsUsing(function (Select $component) {
                    $state = $component->getState() ?? [];
                    $pivots = [];
                    foreach (array_values($state) as $order => $id) {
                        $pivots[$id] = ['sort_order' => $order];
                    }
                    $component->getRecord()->media()->sync($pivots);
                })->preload()->helperText('Upload through Media, then reuse here. Drag selections to set gallery order.'),
            ])->columns(2),
            Section::make('Video')->schema([Select::make('platform')->options(['youtube' => 'YouTube', 'vimeo' => 'Vimeo']), TextInput::make('video_url')->required(static::$kind === 'video')->url()->maxLength(500)->helperText('HTTPS YouTube or Vimeo only; no autoplay or pasted scripts.')])->columns(2),
            Section::make('Publication & presentation')->schema([
                Select::make('publication')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])->default('draft')->required(), Select::make('visibility')->options(['public' => 'Public', 'unlisted' => 'Unlisted', 'private' => 'Private'])->default('private')->required(), Select::make('maturity')->options(['seed' => 'Seed', 'growing' => 'Growing', 'fruit' => 'Fruit'])->default('seed')->required(), DateTimePicker::make('published_at'), Toggle::make('featured')->label('Feature in collections'), Toggle::make('portfolio')->label('Include in Work (projects only)'), TextInput::make('sort_order')->numeric()->default(0), DateTimePicker::make('meaningful_updated_at'), KeyValue::make('case_study')->label('Case study')->helperText('Use context, contribution, process, deliverables, outcome, lessons, credits and evidence. Label demonstration or concept work.'), TextInput::make('seo_title')->maxLength(180), Textarea::make('seo_description')->maxLength(300),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable()->sortable(), TextColumn::make('type')->badge(), TextColumn::make('publication')->badge(), TextColumn::make('visibility')->badge(), TextColumn::make('maturity'), IconColumn::make('portfolio')->boolean(), TextColumn::make('updated_at')->since()])->filters([SelectFilter::make('publication')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived']), SelectFilter::make('visibility')->options(['public' => 'Public', 'unlisted' => 'Unlisted', 'private' => 'Private']), TrashedFilter::make()])->recordActions([EditAction::make(), Action::make('preview')->url(fn (Entry $r) => URL::temporarySignedRoute('entry.preview', now()->addMinutes(20), ['entry' => $r->id]))->openUrlInNewTab(), DeleteAction::make(), RestoreAction::make()])->defaultSort('updated_at', 'desc');
    }
}
