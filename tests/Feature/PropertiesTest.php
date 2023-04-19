<?php

use App\Models\City;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\{actingAs};
use function Pest\Laravel\{assertDatabaseHas};

test('property owner has access to properties feature', function () {
    actingAs(createOwner())
        ->getJson('/api/owner/properties')
        ->assertStatus(200);
});

test('user does not have access to properties feature', function () {
    actingAs(createUser())
        ->getJson('/api/owner/properties')
        ->assertStatus(403);
});

test('property owner can add property', function () {
    actingAs(createOwner())
        ->postJson('/api/owner/properties', [
            'name' => 'My property',
            'city_id' => City::value('id'),
            'address_street' => 'Street Address 1',
            'address_postcode' => '12345',
        ])
        ->assertSuccessful()
        ->assertJsonFragment(['name' => 'My property']);
});

test('property owner can add photo to property', function () {
    Storage::fake();

    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);

    actingAs($owner)
        ->postJson('/api/owner/properties/' . $property->id . '/photos', [
            'photo' => UploadedFile::fake()->image('photo.png')
        ])
        ->assertStatus(200)
        ->assertJsonFragment([
            'filename' => config('app.url') . '/storage/1/photo.png',
            'thumbnail' => config('app.url') . '/storage/1/conversions/photo-thumbnail.jpg',
        ]);
});

test('property owner can reorder photos in property', function () {
    Storage::fake();

    $owner = createOwner();
    $cityId = City::value('id');
    $property = Property::factory()->withImages()->create([
        'owner_id' => $owner->id,
        'city_id' => $cityId,
    ]);

    $mediaCollection = $property->getMedia('images');

    $photo1 = $mediaCollection->first();
    $photo2 = $mediaCollection->last();

    $newPosition = $photo1->position + 1;

    actingAs($owner)
        ->postJson('/api/owner/properties/' . $property->id . '/photos/1/reorder/' . $newPosition)
        ->assertStatus(200)
        ->assertJsonFragment(['newPosition' => $newPosition]);

    assertDatabaseHas('media', ['file_name' => $photo1->file_name, 'position' => $newPosition]);
    assertDatabaseHas('media', ['file_name' => $photo2->file_name, 'position' => $photo1->position]);
});
