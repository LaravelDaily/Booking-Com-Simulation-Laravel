<?php

use App\Models\Property;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $allProperties = Property::query()
            ->withAvg('bookings', 'rating')
            ->whereNull('bookings_avg_rating')
            ->get(['id']); // We don't need the existing field to update the data

        foreach ($allProperties as $property) {
            if ($property->bookings_avg_rating) {
                Property::where('id', $property->id)->update(['bookings_avg_rating' => $property->bookings_avg_rating]);
            }
        }
    }
};
