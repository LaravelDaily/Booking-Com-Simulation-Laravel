<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geoobject>
 */
class GeoobjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::inRandomOrder()->value('id'),
            'name' => fake()->name(),
            'lat' => fake()->latitude(),
            'long' => fake()->longitude(),
        ];
    }
}
