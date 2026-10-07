<?php

namespace App\Filament\Resources;

class ArticleResource extends EntryResource
{
    protected static ?string $kind = 'article';

    protected static ?string $navigationLabel = 'Blogs & Guides';

    public static function getPages(): array
    {
        return ['index' => Pages\ManageArticles::route('/')];
    }
}
