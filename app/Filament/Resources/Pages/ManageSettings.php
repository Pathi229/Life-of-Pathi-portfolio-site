<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->visible(fn () => ! Setting::exists())];
    }
}
