<?php

use App\Models\City;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;


test('property owner has access to properties feature', function () {
    $owner = User::factory()->owner()->create();
    $response = $this->actingAs($owner)->getJson('/api/owner/properties');

    $response->assertStatus(200);
});

test('user does not have access to properties feature', function () {
    $user = User::factory()->user()->create();
    $response = $this->actingAs($user)->getJson('/api/owner/properties');

    $response->assertStatus(403);
});

test('property owner can add property', function () {
    $owner = User::factory()->owner()->create();
    $response = $this->actingAs($owner)->postJson('/api/owner/properties', [
        'name' => 'My property',
        'city_id' => City::value('id'),
        'address_street' => 'Street Address 1',
        'address_postcode' => '12345',
    ]);

    $response->assertSuccessful();
    $response->assertJsonFragment(['name' => 'My property']);
});

test('property owner can add photo to property', function () {
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
});

test('property owner can reorder photos in property', function () {
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
});
