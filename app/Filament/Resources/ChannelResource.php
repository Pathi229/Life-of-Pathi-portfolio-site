<?php

namespace App\Filament\Resources;

use App\Models\Channel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $s): Schema
    {
        return $s->components([TextInput::make('name')->required(), TextInput::make('slug')->required()->unique(ignoreRecord: true)->regex('/^[a-z0-9-]+$/'), Textarea::make('introduction'), Select::make('cover_media_id')->relationship('cover', 'name')->preload(), ColorPicker::make('accent')->default('#3e6753'), KeyValue::make('social_links')->helperText('Label => HTTPS URL'), Select::make('branches')->relationship('branches', 'name')->multiple()->preload()]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([TextColumn::make('name')->searchable(), TextColumn::make('updated_at')->since()])->filters([TrashedFilter::make()])->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageChannels::route('/')];
    }
}
