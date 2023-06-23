<?php

namespace Database\Factories;

use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->text(20),
            'capacity_adults' => rand(1, 5),
            'capacity_children' => rand(1, 5),
            'wheelchair_access' => fake()->boolean(),
            'pets_allowed' => fake()->boolean(),
            'smoking_allowed' => fake()->boolean(),
            'free_cancellation' => fake()->boolean(),
            'all_day_access' => fake()->boolean()
        ];
    }
}
