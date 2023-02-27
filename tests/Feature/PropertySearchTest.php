<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Geoobject;
use App\Models\Property;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_search_by_city_returns_correct_results(): void
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $cities = City::take(2)->pluck('id');
        $propertyInCity = Property::factory()->create(['owner_id' => $owner->id, 'city_id' => $cities[0]]);
        $propertyInAnotherCity = Property::factory()->create(['owner_id' => $owner->id, 'city_id' => $cities[1]]);

        $response = $this->getJson('/api/search?city=' . $cities[0]);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $propertyInCity->id]);
    }

    public function test_property_search_by_country_returns_correct_results(): void
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $countries = Country::with('cities')->take(2)->get();
        $propertyInCountry = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $countries[0]->cities()->value('id')
        ]);
        $propertyInAnotherCountry = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $countries[1]->cities()->value('id')
        ]);

        $response = $this->getJson('/api/search?country=' . $countries[0]->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $propertyInCountry->id]);
    }

    public function test_property_search_by_geoobject_returns_correct_results(): void
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
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

        $response = $this->getJson('/api/search?geoobject=' . $geoobject->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $propertyNear->id]);
    }

    public function test_property_search_by_capacity_returns_correct_results(): void
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $cityId = City::value('id');
        $propertyWithSmallRoom = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);
        Room::factory()->create([
            'property_id' => $propertyWithSmallRoom->id,
            'capacity_adults' => 1,
            'capacity_children' => 0,
        ]);
        $propertyWithLargeRoom = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);
        Room::factory()->create([
            'property_id' => $propertyWithLargeRoom->id,
            'capacity_adults' => 3,
            'capacity_children' => 2,
        ]);

        $response = $this->getJson('/api/search?city=' . $cityId . '&adults=2&children=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $propertyWithLargeRoom->id]);
    }

    public function test_property_search_by_capacity_returns_only_suitable_rooms(): void
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $user = User::factory()->create(['role_id' => Role::ROLE_USER]);
        $cityId = City::value('id');
        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);
        $smallRoom = Room::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 1,
            'capacity_children' => 0,
        ]);
        $largeRoom = Room::factory()->create([
            'property_id' => $property->id,
            'capacity_adults' => 3,
            'capacity_children' => 2,
        ]);

        $response = $this->getJson('/api/search?city=' . $cityId . '&adults=2&children=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonCount(1, '0.rooms');
        $response->assertJsonPath('0.rooms.0.id', $largeRoom->id);
    }
}
