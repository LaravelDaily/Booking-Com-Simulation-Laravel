<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGuest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingGuestFactory extends Factory
{
    protected $model = BookingGuest::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
