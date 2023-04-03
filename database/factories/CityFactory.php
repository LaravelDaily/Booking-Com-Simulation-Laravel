<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'lat' => $this->faker->latitude(),
            'long' => $this->faker->longitude(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'country_id' => Country::factory(),
        ];
    }
}
