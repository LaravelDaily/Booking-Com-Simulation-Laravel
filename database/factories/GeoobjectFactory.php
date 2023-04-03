<?php

namespace Database\Factories;

use App\Models\Geoobject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class GeoobjectFactory extends Factory
{
    protected $model = Geoobject::class;

    public function definition(): array
    {
        return [
            'city_id' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'lat' => $this->faker->latitude(),
            'long' => $this->faker->longitude(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
