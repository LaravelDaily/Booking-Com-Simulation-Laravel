<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\City;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyRatingTest extends TestCase
{
    use RefreshDatabase;

    private function create_apartment(): Apartment
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $cityId = City::value('id');
        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);

        return Apartment::create([
            'name' => 'Apartment',
            'property_id' => $property->id,
            'capacity_adults' => 3,
            'capacity_children' => 2,
        ]);
    }

    public function test_rating_a_booking_updates_property_rating(): void
    {
        $user1 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $apartment = $this->create_apartment();
        $booking = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 1,
            'guests_children' => 0,
        ]);

        $booking2 = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 1,
            'guests_children' => 0,
        ]);

        $correctData = [
            'rating' => 10,
            'review_comment' => 'Comment with a good length to be accepted.'
        ];
        $response = $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, $correctData);
        $response->assertStatus(200);
        $response->assertJsonFragment($correctData);

        $property = Property::find($apartment->property_id);
        $this->assertEquals(10, $property->bookings_avg_rating);


        $correctData = [
            'rating' => 5,
            'review_comment' => 'Comment with a good length to be accepted.'
        ];
        $response = $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking2->id, $correctData);
        $response->assertStatus(200);
        $response->assertJsonFragment($correctData);

        $property = Property::find($apartment->property_id);
        $this->assertEquals(7.5, $property->bookings_avg_rating);
    }
}
