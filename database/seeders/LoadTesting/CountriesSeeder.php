<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        if (Country::count() < 100) {
            Country::factory()
                ->count(100)
                ->create();
        }
    }
}
