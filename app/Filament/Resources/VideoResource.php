<?php

namespace App\Filament\Resources;

class VideoResource extends EntryResource
{
    protected static ?string $kind = 'video';

    protected static ?string $navigationLabel = 'Videos';

    public static function getPages(): array
    {
        return ['index' => Pages\ManageVideos::route('/')];
    }
}
