<?php

namespace Database\Seeders\Performance;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $withRatings = 100, int $withoutRatings = 100): void
    {
        $users = User::where('role_id', Role::ROLE_USER)->pluck('id');
        $apartmentMin = Apartment::min('id');
        $apartmentMax = Apartment::max('id');

        $bookings = [];
        for ($i = 1; $i <= $withoutRatings; $i++) {
            $startDate = now()->addDays(rand(1, 200));
            $bookings[] = [
                'apartment_id' => rand($apartmentMin, $apartmentMax),
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $startDate->addDays(rand(2,7))->toDateTimeString(),
                'guests_adults' => rand(1, 5),
                'guests_children' => rand(1, 5),
                'total_price' => rand(100, 2000),
                'user_id' => $users->random(),
                'rating' => null,
            ];

            if ($i % 500 == 0 || $i == $withoutRatings) {
                Booking::insert($bookings);
                $bookings = [];
            }
        }

        for ($i = 1; $i <= $withRatings; $i++) {
            $startDate = now()->addDays(rand(1, 200));
            $bookings[] = [
                'apartment_id' => rand($apartmentMin, $apartmentMax),
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $startDate->addDays(rand(2,7))->toDateTimeString(),
                'guests_adults' => rand(1, 5),
                'guests_children' => rand(1, 5),
                'total_price' => rand(100, 2000),
                'user_id' => $users->random(),
                'rating' => random_int(1, 10),
            ];

            if ($i % 500 == 0 || $i == $withoutRatings) {
                Booking::insert($bookings);
                $bookings = [];
            }
        }
    }
}
