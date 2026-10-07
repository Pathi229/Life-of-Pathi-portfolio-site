<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMedias extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
