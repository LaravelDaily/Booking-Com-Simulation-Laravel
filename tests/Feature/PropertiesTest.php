<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_owner_has_access_to_properties_feature()
    {
        $owner = User::factory()->owner()->create();
        $response = $this->actingAs($owner)->getJson('/api/owner/properties');

        $response->assertStatus(200);
    }

    public function test_user_does_not_have_access_to_properties_feature()
    {
        $user = User::factory()->user()->create();
        $response = $this->actingAs($user)->getJson('/api/owner/properties');

        $response->assertStatus(403);
    }

    public function test_property_owner_can_add_property()
    {
        $owner = User::factory()->owner()->create();
        $response = $this->actingAs($owner)->postJson('/api/owner/properties', [
            'name' => 'My property',
            'city_id' => City::value('id'),
            'address_street' => 'Street Address 1',
            'address_postcode' => '12345',
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment(['name' => 'My property']);
    }

    public function test_property_owner_can_add_photo_to_property()
    {
        Storage::fake();

        $owner = User::factory()->owner()->create();
        $cityId = City::value('id');
        $property = Property::factory()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);

        $response = $this->actingAs($owner)->postJson('/api/owner/properties/' . $property->id . '/photos', [
            'photo' => UploadedFile::fake()->image('photo.png')
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'filename' => config('app.url') . '/storage/1/photo.png',
            'thumbnail' => config('app.url') . '/storage/1/conversions/photo-thumbnail.jpg',
        ]);
    }

    public function test_property_owner_can_reorder_photos_in_property()
    {
        Storage::fake();

        $owner = User::factory()->owner()->create();
        $cityId = City::value('id');
        $property = Property::factory()->withImages()->create([
            'owner_id' => $owner->id,
            'city_id' => $cityId,
        ]);

        $mediaCollection = $property->getMedia('images');

        $photo1 = $mediaCollection->first();
        $photo2 = $mediaCollection->last();

        $newPosition = $photo1->position + 1;
        $response = $this->actingAs($owner)->postJson('/api/owner/properties/' . $property->id . '/photos/1/reorder/' . $newPosition);
        $response->assertStatus(200);
        $response->assertJsonFragment(['newPosition' => $newPosition]);

        $this->assertDatabaseHas('media', ['file_name' => $photo1->file_name, 'position' => $newPosition]);
        $this->assertDatabaseHas('media', ['file_name' => $photo2->file_name, 'position' => $photo1->position]);
    }
}
