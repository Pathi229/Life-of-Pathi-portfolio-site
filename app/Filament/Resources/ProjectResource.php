<?php

namespace App\Filament\Resources;

class ProjectResource extends EntryResource
{
    protected static ?string $kind = 'project';

    protected static ?string $navigationLabel = 'Projects';

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProjects::route('/')];
    }
}
