<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Apartment;
use App\Models\Property;
use Illuminate\Database\Seeder;

class ApartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::pluck('id');

        for ($i = 0; $i <= 20_000; $i++) {
            Apartment::factory()
                ->create([
                    'property_id' => $properties->random(),
                ]);
        }
    }
}