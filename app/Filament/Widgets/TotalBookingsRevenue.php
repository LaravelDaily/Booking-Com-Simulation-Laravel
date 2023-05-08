<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\LineChartWidget;

class TotalBookingsRevenue extends LineChartWidget
{
    protected static ?string $heading = 'Total bookings revenue for the last 30 days';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Trend::model(Booking::class)
            ->between(
                start: now()->subMonth()->endOfDay(),
                end: now(),
            )
            ->perDay()
            ->sum('total_price');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }
}
