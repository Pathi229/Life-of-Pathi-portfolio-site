<?php

namespace App\Filament\Resources;

use App\Models\Media;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $s): Schema
    {
        return $s->components([TextInput::make('name')->required(), FileUpload::make('path')->label('Upload')->disk('local')->directory('media')->visibility('private')->getUploadedFileUsing(function (FileUpload $component, string $file) {
            $disk = Storage::disk('local');
            $record = $component->getRecord();
            if (! $record || ! $disk->exists($file)) {
                return null;
            }

            return ['name' => $record->name, 'size' => $disk->size($file), 'type' => $disk->mimeType($file), 'url' => route('media', $record->id)];
        })->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'text/plain'])->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeTargetHeight('1600')->imageResizeUpscale(false)->maxSize(10240)->required()->helperText('Private storage. Files are served only through authorised content routes.'), Textarea::make('alt')->label('Image alt text')->required(), Textarea::make('caption'), TextInput::make('credit')]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([TextColumn::make('name')->searchable(), TextColumn::make('updated_at')->since()])->filters([TrashedFilter::make()])->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMedias::route('/')];
    }
}
