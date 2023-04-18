<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class PricingService
{
    public function calculateApartmentPriceForDates(
        Collection $apartmentPrices,
        ?string $startDate,
        ?string $endDate): int
    {
        $cost = 0;

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        while ($startDate->lte($endDate)) {
            $cost += $apartmentPrices->where(function ($price) use ($startDate) {
                return Carbon::parse($price['start_date'])->lte($startDate)
                    && Carbon::parse($price['end_date'])->gte($startDate);
            })->value('price');
            $startDate->addDay();
        }

        return $cost;
    }
}
