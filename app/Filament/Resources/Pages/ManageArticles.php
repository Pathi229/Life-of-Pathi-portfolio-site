<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageArticles extends ManageRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
