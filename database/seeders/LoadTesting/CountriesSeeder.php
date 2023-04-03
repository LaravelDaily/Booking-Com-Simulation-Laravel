<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        Country::factory()
            ->count(100)
            ->create();
    }
}
