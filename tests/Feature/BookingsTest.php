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

class BookingsTest extends TestCase
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

    public function test_user_can_get_only_their_bookings()
    {
        $user1 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $user2 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $apartment = $this->create_apartment();
        $booking1 = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 1,
            'guests_children' => 0,
        ]);
        $booking2 = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user2->id,
            'start_date' => now()->addDay(3),
            'end_date' => now()->addDays(4),
            'guests_adults' => 2,
            'guests_children' => 1,
        ]);

        $response = $this->actingAs($user1)->getJson('/api/user/bookings');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['guests_adults' => 1]);

        $response = $this->actingAs($user1)->getJson('/api/user/bookings/' . $booking1->id);
        $response->assertStatus(200);
        $response->assertJsonFragment(['guests_adults' => 1]);

        $response = $this->actingAs($user1)->getJson('/api/user/bookings/' . $booking2->id);
        $response->assertStatus(403);
    }

    public function test_property_owner_does_not_have_access_to_bookings_feature()
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $response = $this->actingAs($owner)->getJson('/api/user/bookings');

        $response->assertStatus(403);
    }

    public function test_user_can_book_apartment_successfully_but_not_twice()
    {
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $apartment = $this->create_apartment();

        $bookingParameters = [
            'apartment_id' => $apartment->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 2,
            'guests_children' => 1,
        ];
        $response = $this->actingAs($user)->postJson('/api/user/bookings', $bookingParameters);
        $response->assertStatus(201);

        $response = $this->actingAs($user)->postJson('/api/user/bookings', $bookingParameters);
        $response->assertStatus(422);

        $bookingParameters['start_date'] = now()->addDays(3);
        $bookingParameters['end_date'] = now()->addDays(4);
        $bookingParameters['guests_adults'] = 5;
        $response = $this->actingAs($user)->postJson('/api/user/bookings', $bookingParameters);
        $response->assertStatus(422);
    }

    public function test_user_can_cancel_their_booking_but_still_view_it()
    {
        $user1 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $user2 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $apartment = $this->create_apartment();
        $booking = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 1,
            'guests_children' => 0,
        ]);

        $response = $this->actingAs($user2)->deleteJson('/api/user/bookings/' . $booking->id);
        $response->assertStatus(403);

        $response = $this->actingAs($user1)->deleteJson('/api/user/bookings/' . $booking->id);
        $response->assertStatus(204);

        $response = $this->actingAs($user1)->getJson('/api/user/bookings');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['cancelled_at' => now()->toDateString()]);

        $response = $this->actingAs($user1)->getJson('/api/user/bookings/' . $booking->id);
        $response->assertStatus(200);
        $response->assertJsonFragment(['cancelled_at' => now()->toDateString()]);
    }

    public function test_user_can_post_rating_for_their_booking()
    {
        $user1 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $user2 = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $apartment = $this->create_apartment();
        $booking = Booking::create([
            'apartment_id' => $apartment->id,
            'user_id' => $user1->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'guests_adults' => 1,
            'guests_children' => 0,
        ]);

        $response = $this->actingAs($user2)->putJson('/api/user/bookings/' . $booking->id, []);
        $response->assertStatus(403);

        $response = $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, [
            'rating' => 11
        ]);
        $response->assertStatus(422);

        $response = $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, [
            'rating' => 10,
            'review_comment' => 'Too short comment.'
        ]);
        $response->assertStatus(422);

        $correctData = [
            'rating' => 10,
            'review_comment' => 'Comment with a good length to be accepted.'
        ];
        $response = $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, $correctData);
        $response->assertStatus(200);
        $response->assertJsonFragment($correctData);
    }
}
