<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\SeriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSeries extends ManageRecords
{
    protected static string $resource = SeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
