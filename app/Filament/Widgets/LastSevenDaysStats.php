<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class LastSevenDaysStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Last 7 days new properties', Property::where('created_at', '>', now()->subDays(7)->endOfDay())->count()),
            Card::make('Last 7 days new users', User::where('created_at', '>', now()->subDays(7)->endOfDay())->count())
        ];
    }
}
