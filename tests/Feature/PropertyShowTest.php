<?php

use App\Models\Apartment;
use App\Models\City;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Property;
use function Pest\Laravel\{getJson};

test('property show loads property correctly', function () {
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

    $facilityCategory = FacilityCategory::create([
        'name' => 'Some category'
    ]);
    $facility = Facility::create([
        'category_id' => $facilityCategory->id,
        'name' => 'Some facility'
    ]);
    $midSizeApartment->facilities()->attach($facility->id);

    getJson('/api/properties/' . $property->id)
        ->assertStatus(200)
        ->assertJsonCount(3, 'apartments')
        ->assertJsonPath('name', $property->name);

    getJson('/api/properties/' . $property->id . '?adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonCount(2, 'apartments')
        ->assertJsonPath('name', $property->name)
        ->assertJsonPath('apartments.0.facilities.0.name', $facility->name)
        ->assertJsonCount(0, 'apartments.1.facilities');

    getJson('/api/search?city=' . $cityId . '&adults=2&children=1')
        ->assertStatus(200)
        ->assertJsonPath('properties.0.apartments.0.facilities', NULL);
});
