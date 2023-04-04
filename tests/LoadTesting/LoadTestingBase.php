<?php

namespace Tests\LoadTesting;

use App\Models\Booking;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LoadSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class LoadTestingBase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        $bookingsCount = Booking::count();
        if ($bookingsCount !== 1_000_000) {
            $this->artisan('migrate:fresh');
            $this->seed(DatabaseSeeder::class);
            $this->seed(LoadSeeder::class);
        }
    }
}