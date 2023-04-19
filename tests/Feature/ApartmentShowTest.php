<?php

use App\Models\Apartment;
use App\Models\City;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Property;
use function Pest\Laravel\getJson;

test('apartment show loads apartment with facilities', function () {
    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);
    $apartment = Apartment::factory()->create([
        'name' => 'Large apartment',
        'property_id' => $property->id,
        'capacity_adults' => 3,
        'capacity_children' => 2,
    ]);

    $firstCategory = FacilityCategory::create([
        'name' => 'First category'
    ]);
    $secondCategory = FacilityCategory::create([
        'name' => 'Second category'
    ]);
    $firstFacility = Facility::create([
        'category_id' => $firstCategory->id,
        'name' => 'First facility'
    ]);
    $secondFacility = Facility::create([
        'category_id' => $firstCategory->id,
        'name' => 'Second facility'
    ]);
    $thirdFacility = Facility::create([
        'category_id' => $secondCategory->id,
        'name' => 'Third facility'
    ]);
    $apartment->facilities()->attach([
        $firstFacility->id, $secondFacility->id, $thirdFacility->id
    ]);

    $expectedFacilityArray = [
        $firstCategory->name => [
            $firstFacility->name,
            $secondFacility->name
        ],
        $secondCategory->name => [
            $thirdFacility->name
        ]
    ];

    getJson('/api/apartments/' . $apartment->id)
        ->assertStatus(200)
        ->assertJsonPath('name', $apartment->name)
        ->assertJsonCount(2, 'facility_categories')
        ->assertJsonFragment($expectedFacilityArray);
});
