<?php

namespace App\Filament\Widgets;

use App\Models\Entry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GardenOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [Stat::make('Drafts', Entry::where('publication', 'draft')->count()), Stat::make('Published', Entry::where('publication', 'published')->count()), Stat::make('Growing projects', Entry::where('type', 'project')->where('maturity', 'growing')->count())];
    }
}
