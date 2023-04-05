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
        $noRating = Booking::whereNull('rating')->count();
        if ($noRating < 500_000) {

            for ($i = 0; $i < 500_000 - $noRating; $i++) {
                Booking::factory()
                    ->create([
                        'apartment_id' => $apartments->random(),
                        'user_id' => $users->random(),
                        'rating' => null
                    ]);
            }
        }

        $hasRating = Booking::whereNotNull('rating')->count();
        if ($hasRating < 500_000) {
            for ($i = 0; $i < 500_000 - $hasRating; $i++) {
                Booking::factory()
                    ->create([
                        'apartment_id' => $apartments->random(),
                        'user_id' => $users->random(),
                        'rating' => random_int(1, 10)
                    ]);
            }
        }
    }
}
