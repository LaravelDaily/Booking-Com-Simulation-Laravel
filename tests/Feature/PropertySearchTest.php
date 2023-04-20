<?php

use App\Models\Apartment;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Facility;
use App\Models\Geoobject;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use function Pest\Laravel\{getJson};

test('property search by city returns correct results', function () {
    $owner = createOwner();
    $cities = City::take(2)->pluck('id');
    $propertyInCity = Property::factory()->create(['owner_id' => $owner->id, 'city_id' => $cities[0]]);
    Property::factory()->create(['owner_id' => $owner->id, 'city_id' => $cities[1]]);

    getJson('/api/search?city=' . $cities[0])
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonFragment(['id' => $propertyInCity->id]);
});

test('property search by country returns correct results', function () {
    $owner = createOwner();
    $countries = Country::with('cities')->take(2)->get();
    $propertyInCountry = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $countries[0]->cities()->value('id')
    ]);
    Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $countries[1]->cities()->value('id')
    ]);

    getJson('/api/search?country=' . $countries[0]->id)
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonFragment(['id' => $propertyInCountry->id]);
});

test('property search by geoobject returns correct results', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $geoobject = Geoobject::first();
    $propertyNear = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
        'lat' => $geoobject->lat,
        'long' => $geoobject->long,
    ]);
    $propertyFar = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
        'lat' => $geoobject->lat + 10,
        'long' => $geoobject->long - 10,
    ]);

    getJson('/api/search?geoobject=' . $geoobject->id)
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonFragment(['id' => $propertyNear->id]);
});

test('property search by capacity returns correct results', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $propertyWithSmallApartment = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    Apartment::factory()->create([
        'property_id' => $propertyWithSmallApartment->id,
        'capacity_adults' => 1,
        'capacity_children' => 0,
    ]);
    $propertyWithLargeApartment = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    Apartment::factory()->create([
        'property_id' => $propertyWithLargeApartment->id,
        'capacity_adults' => 3,
        'capacity_children' => 2,
    ]);

    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonFragment(['id' => $propertyWithLargeApartment->id]);
});

test('property search by capacity returns only suitable apartments', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $smallApartment = Apartment::factory()->create([
        'name' => 'Small apartment',
        'property_id' => $property->id,
        'capacity_adults' => 1,
        'capacity_children' => 0,
    ]);
    $largeApartment = Apartment::factory()->create([
        'name' => 'Large apartment',
        'property_id' => $property->id,
        'capacity_adults' => 3,
        'capacity_children' => 2,
    ]);

    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonCount(1, 'properties.data.0.apartments')
        ->assertJsonPath('properties.data.0.apartments.0.name', $largeApartment->name);
});

test('property search beds list all cases', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $roomTypes = RoomType::all();
    $bedTypes = BedType::all();

    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment = Apartment::factory()->create([
        'name' => 'Small apartment',
        'property_id' => $property->id,
        'capacity_adults' => 1,
        'capacity_children' => 0,
    ]);

    // ----------------------
    // FIRST: check that bed list if empty if no beds
    // ----------------------

    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data')
        ->assertJsonCount(1, 'properties.data.0.apartments')
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '');

    // ----------------------
    // SECOND: create 1 room with 1 bed
    // ----------------------

    $room = Room::create([
        'apartment_id' => $apartment->id,
        'room_type_id' => $roomTypes[0]->id,
        'name' => 'Bedroom',
    ]);
    Bed::create([
        'room_id' => $room->id,
        'bed_type_id' => $bedTypes[0]->id,
    ]);

    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '1 ' . $bedTypes[0]->name);

    // ----------------------
    // THIRD: add another bed to the same room
    // ----------------------

    Bed::create([
        'room_id' => $room->id,
        'bed_type_id' => $bedTypes[0]->id,
    ]);
    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '2 ' . str($bedTypes[0]->name)->plural());

    // ----------------------
    // FOURTH: add second room with no beds
    // ----------------------

    $secondRoom = Room::create([
        'apartment_id' => $apartment->id,
        'room_type_id' => $roomTypes[0]->id,
        'name' => 'Living room',
    ]);
    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '2 ' . str($bedTypes[0]->name)->plural());

    // ----------------------
    // FIFTH: add one bed to that second room
    // ----------------------

    Bed::create([
        'room_id' => $room->id,
        'bed_type_id' => $bedTypes[0]->id,
    ]);
    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '3 ' . str($bedTypes[0]->name)->plural());

    // ----------------------
    // SIXTH: add another bed with different type to that second room
    // ----------------------

    Bed::create([
        'room_id' => $room->id,
        'bed_type_id' => $bedTypes[1]->id,
    ]);
    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '4 beds (3 ' . str($bedTypes[0]->name)->plural() . ', 1 ' . $bedTypes[1]->name . ')');

    // ----------------------
    // SEVENTH: add second bed with that new type to that second room
    // ----------------------

    Bed::create([
        'room_id' => $room->id,
        'bed_type_id' => $bedTypes[1]->id,
    ]);
    getJson('/api/search?city=' . $cityId)
        ->assertStatus(200)
        ->assertJsonPath('properties.data.0.apartments.0.beds_list', '5 beds (3 ' . str($bedTypes[0]->name)->plural() . ', 2 ' . str($bedTypes[1]->name)->plural() . ')');
});

test('property search returns one best apartment per property', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $largeApartment = Apartment::factory()->create([
        'name' => 'Large apartment',
        'property_id' => $property->id,
        'capacity_adults' => 3,
        'capacity_children' => 2,
    ]);
    $midSizeApartment = Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $smallApartment = Apartment::factory()->create([
        'name' => 'Small apartment',
        'property_id' => $property->id,
        'capacity_adults' => 1,
        'capacity_children' => 0,
    ]);

    $property2 = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    Apartment::factory()->create([
        'name' => 'Large apartment 2',
        'property_id' => $property2->id,
        'capacity_adults' => 3,
        'capacity_children' => 2,
    ]);
    Apartment::factory()->create([
        'name' => 'Mid size apartment 2',
        'property_id' => $property2->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    Apartment::factory()->create([
        'name' => 'Small apartment 2',
        'property_id' => $property2->id,
        'capacity_adults' => 1,
        'capacity_children' => 0,
    ]);

    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data')
        ->assertJsonCount(1, 'properties.data.0.apartments')
        ->assertJsonCount(1, 'properties.data.1.apartments')
        ->assertJsonPath('properties.data.0.apartments.0.name', $midSizeApartment->name);
});

test('property search filters by facilities', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $property2 = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property2->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);

    // First case - no facilities exist
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');

    // Second case - filter by facility, 0 properties returned
    $facility = Facility::create(['name' => 'First facility']);
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&facilities[]=' . $facility->id)
        ->assertStatus(200)
        ->assertJsonCount(0, 'properties.data');

    // Third case - attach facility to property, filter by facility, 1 property returned
    $property->facilities()->attach($facility->id);
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&facilities[]=' . $facility->id)
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data');

    // Fourth case - attach facility to DIFFERENT property, filter by facility, 2 properties returned
    $property2->facilities()->attach($facility->id);
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&facilities[]=' . $facility->id)
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');
});

test('property search filters by price', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $cheapApartment = Apartment::factory()->create([
        'name' => 'Cheap apartment',
        'property_id' => $property->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $cheapApartment->prices()->create([
        'start_date' => now(),
        'end_date' => now()->addMonth(),
        'price' => 70,
    ]);
    $property2 = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $expensiveApartment = Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property2->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $expensiveApartment->prices()->create([
        'start_date' => now(),
        'end_date' => now()->addMonth(),
        'price' => 130,
    ]);

    // First case - no price range: both returned
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');

    // First case - min price set: 1 returned
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&price_from=100')
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data');

    // Second case - max price set: 1 returned
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&price_to=100')
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data');

    // Third case - both min and max price set: 2 returned
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&price_from=50&price_to=150')
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');

    // Fourth case - both min and max price set narrow: 0 returned
    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&price_from=80&price_to=100')
        ->assertStatus(200)
        ->assertJsonCount(0, 'properties.data');
});

test('properties show correct rating and ordered by it', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment1 = Apartment::factory()->create([
        'name' => 'Cheap apartment',
        'property_id' => $property->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $property2 = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment2 = Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property2->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $user1 = User::factory()->user()->create();
    $user2 = User::factory()->user()->create();
    $booking1 = Booking::create([
        'apartment_id' => $apartment1->id,
        'user_id' => $user1->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
    ]);
    $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking1->id, [
        'rating' => 7
    ]);
    $booking2 = Booking::create([
        'apartment_id' => $apartment2->id,
        'user_id' => $user1->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
    ]);
    $this->actingAs($user1)->putJson('/api/user/bookings/' . $booking2->id, [
        'rating' => 9
    ]);
    $booking3 = Booking::create([
        'apartment_id' => $apartment2->id,
        'user_id' => $user2->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
        'rating' => 7
    ]);
    $this->actingAs($user2)->putJson('/api/user/bookings/' . $booking3->id, [
        'rating' => 7
    ]);

    $response = getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');
    expect($response->json('properties.data')[0]['avg_rating'])->toEqual(8);
    expect($response->json('properties.data')[1]['avg_rating'])->toEqual(7);
});

test('search shows only apartments available for dates', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment1 = Apartment::factory()->create([
        'name' => 'Cheap apartment',
        'property_id' => $property->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $property2 = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment2 = Apartment::factory()->create([
        'name' => 'Mid size apartment',
        'property_id' => $property2->id,
        'capacity_adults' => 2,
        'capacity_children' => 1,
    ]);
    $user1 = User::factory()->user()->create();

    getJson('/api/search?city=' . $cityId . '&adults=2&children=1&start_date=' . now()->addDay() . '&end_date=' . now()->addDays(2))
        ->assertStatus(200)
        ->assertJsonCount(2, 'properties.data');

    Booking::create([
        'apartment_id' => $apartment1->id,
        'user_id' => $user1->id,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(2),
        'guests_adults' => 1,
        'guests_children' => 0,
    ]);

    $response = getJson('/api/search?city=' . $cityId . '&adults=2&children=1&start_date=' . now()->addDay() . '&end_date=' . now()->addDays(2))
        ->assertStatus(200)
        ->assertJsonCount(1, 'properties.data');
    expect($response->json('properties.data')[0]['id'])->toEqual($property2->id);

    $response = getJson('/api/properties/' . $property2->id . '?city=' . $cityId . '&adults=2&children=1&start_date=' . now()->addDay() . '&end_date=' . now()->addDays(2))
        ->assertStatus(200);
    expect($response->json('id'))->toEqual($property2->id);
});
