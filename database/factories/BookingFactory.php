<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomNumber(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'guests_adults' => $this->faker->randomNumber(),
            'guests_children' => $this->faker->randomNumber(),
            'total_price' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'rating' => $this->faker->randomNumber(),
            'review_comment' => $this->faker->word(),

            'apartment_id' => Apartment::factory(),
        ];
    }
}
