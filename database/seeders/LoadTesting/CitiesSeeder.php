<?php

namespace Database\Seeders\LoadTesting;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::pluck('id');

        for ($i = 0; $i < 1_000; $i++) {
            City::factory()
                ->create(['country_id' => $countries->random()]);
        }
    }
}
