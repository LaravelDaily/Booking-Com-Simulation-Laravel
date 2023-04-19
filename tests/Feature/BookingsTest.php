<?php

use App\Models\Booking;
use function Pest\Laravel\{actingAs};

test('user can get only their bookings', function () {
    $user1 = createUser();
    $user2 = createUser();
    $apartment = createApartment();
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

    actingAs($user1)->getJson('/api/user/bookings')
        ->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['guests_adults' => 1]);

    actingAs($user1)->getJson('/api/user/bookings/' . $booking1->id)
        ->assertStatus(200)
        ->assertJsonFragment(['guests_adults' => 1]);

    actingAs($user1)->getJson('/api/user/bookings/' . $booking2->id)
        ->assertStatus(403);
});

test('property owner does not have access to bookings feature', function () {
    asOwner()->getJson('/api/user/bookings')->assertStatus(403);
});

test('user can book apartment successfully but not twice', function () {
    $user = createUser();
    $apartment = createApartment();

    $bookingParameters = [
        'apartment_id' => $apartment->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 2,
        'guests_children' => 1,
    ];
    actingAs($user)->postJson('/api/user/bookings', $bookingParameters)
        ->assertStatus(201);

    actingAs($user)->postJson('/api/user/bookings', $bookingParameters)
        ->assertStatus(422);

    $bookingParameters['start_date'] = now()->addDays(3);
    $bookingParameters['end_date'] = now()->addDays(4);
    $bookingParameters['guests_adults'] = 5;

    actingAs($user)->postJson('/api/user/bookings', $bookingParameters)
        ->assertStatus(422);
});

test('user can cancel their booking but still view it', function () {
    $user1 = createUser();
    $user2 = createUser();
    $apartment = createApartment();
    $booking = Booking::create([
        'apartment_id' => $apartment->id,
        'user_id' => $user1->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
    ]);

    actingAs($user2)->deleteJson('/api/user/bookings/' . $booking->id)
        ->assertStatus(403);

    actingAs($user1)->deleteJson('/api/user/bookings/' . $booking->id)
        ->assertStatus(204);

    actingAs($user1)->getJson('/api/user/bookings')
        ->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['cancelled_at' => now()->toDateString()]);

    actingAs($user1)->getJson('/api/user/bookings/' . $booking->id)
        ->assertStatus(200)
        ->assertJsonFragment(['cancelled_at' => now()->toDateString()]);
});

test('user can post rating for their booking', function () {
    $user1 = createUser();
    $user2 = createUser();
    $apartment = createApartment();
    $booking = Booking::create([
        'apartment_id' => $apartment->id,
        'user_id' => $user1->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
    ]);

    actingAs($user2)->putJson('/api/user/bookings/' . $booking->id, [])
        ->assertStatus(403);

    actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, [
        'rating' => 11
    ])
        ->assertStatus(422);

    actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, [
        'rating' => 10,
        'review_comment' => 'Too short comment.'
    ])
        ->assertStatus(422);

    $correctData = [
        'rating' => 10,
        'review_comment' => 'Comment with a good length to be accepted.'
    ];
    actingAs($user1)->putJson('/api/user/bookings/' . $booking->id, $correctData)
        ->assertStatus(200)
        ->assertJsonFragment($correctData);
});