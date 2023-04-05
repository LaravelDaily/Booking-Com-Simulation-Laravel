<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Apartment;
use App\Models\Property;
use Illuminate\Database\Seeder;

class ApartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $count = Apartment::count();
        if ($count < 100_000) {
            $properties = Property::pluck('id');

            for ($i = 0; $i <= 100_000 - $count; $i++) {
                Apartment::factory()
                    ->create([
                        'property_id' => $properties->random(),
                    ]);
            }
        }
    }
}
