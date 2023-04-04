<?php

namespace Database\Seeders\LoadTesting;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingsSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = Apartment::pluck('id');
        $users = User::where('role_id', Role::ROLE_USER)->pluck('id');

        for ($i = 0; $i < 500_000; $i++) {
            Booking::factory()
                ->create([
                    'apartment_id' => $apartments->random(),
                    'user_id' => $users->random(),
                    'rating' => null
                ]);
        }

        for ($i = 0; $i < 500_000; $i++) {
            Booking::factory()
                ->create([
                    'apartment_id' => $apartments->random(),
                    'user_id' => $users->random(),
                    'rating' => random_int(1, 10)
                ]);
        }
    }
}
