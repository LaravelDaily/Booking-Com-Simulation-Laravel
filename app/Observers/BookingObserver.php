<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    public function creating(Booking $booking)
    {
        $booking->total_price = $booking->apartment->calculatePriceForDates(
            $booking->start_date,
            $booking->end_date
        );
    }
}
