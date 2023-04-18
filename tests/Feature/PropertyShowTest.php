<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\City;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_show_loads_property_correctly()
    {
        $owner = User::factory()->owner()->create();
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

        $response = $this->getJson('/api/properties/'.$property->id);
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'apartments');
        $response->assertJsonPath('name', $property->name);

        $response = $this->getJson('/api/properties/'.$property->id.'?adults=2&children=1');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'apartments');
        $response->assertJsonPath('name', $property->name);
        $response->assertJsonPath('apartments.0.facilities.0.name', $facility->name);
        $response->assertJsonCount(0, 'apartments.1.facilities');

        $response = $this->getJson('/api/search?city=' . $cityId . '&adults=2&children=1');
        $response->assertStatus(200);
        $response->assertJsonPath('properties.0.apartments.0.facilities', NULL);
    }
}
