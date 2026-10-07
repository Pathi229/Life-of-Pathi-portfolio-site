<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PublishingShortcuts extends Widget
{
    protected string $view = 'filament.widgets.publishing-shortcuts';

    protected int|string|array $columnSpan = 'full';
}
