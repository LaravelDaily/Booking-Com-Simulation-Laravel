Now it's time to take care of the **photos** for the properties. Each property may have multiple photos, assign order to them, and one of them should be marked "main". Simple, right?

---

## Preparing For Photo Upload

Personally, I'm a big fan of the package [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) to manage images. So we will try to use exactly that one.

Four terminal commands for that one:

```sh
composer require "spatie/laravel-medialibrary:^10.0.0"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"
```

It will create a new `media` DB table to handle images with polymorphic relations. That kind of relationships fits our project really well because currently our images are attached to properties, but maybe in the future they will be attached to other DB tables, like apartments or rooms.

Next, we will enable the Media Library for the model, where we want to attach the files, which is Property.

**app/Models/Property.php**:
```php
// ...
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Property extends Model implements HasMedia
{
    use InteractsWithMedia;

    // ...
```

---

## Endpoint to Upload Photo

Next, we will build an API endpoint to upload a photo to the property.

```sh
php artisan make:controller Owner/PropertyPhotoController
```

We will use Route Model binding for the property, so the route would look like this:

**routes/api.php**:
```php
Route::prefix('owner')->group(function () {
    // ... older routes of the owner

    Route::post('properties/{property}/photos',
        [\App\Http\Controllers\Owner\PropertyPhotoController::class, 'store']);
});
```

And this is the main code of the Controller method:

**app/Http/Controllers/Owner/PropertyPhotoController.php**:
```php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyPhotoController extends Controller
{
    public function store(Property $property, Request $request)
    {
    	// ...
    }
}
```

Now, let's fill in that Controller method with what it should do:

- Security check: property should belong to the logged-in user, so no one would upload the file to someone else's property
- Validation: file should be image with 5 MB size max
- Upload the file and assign it to the collection with Media Library


Here's the code for all of it:

**app/Http/Controllers/Owner/PropertyPhotoController.php**:
```php
public function store(Property $property, Request $request)
{
    $request->validate([
        'photo' => ['image', 'max:5000']
    ]);

    if ($property->owner_id != auth()->id()) {
        abort(403);
    }

    $photo = $property->addMediaFromRequest('photo')->toMediaCollection('photos');

    return [
        'filename' => $photo->getUrl(),
    ];
}
```

There's an open question about what that Controller method should return. I decided that it should return the full URL of the uploaded file.

And that's it: here's the result in the Postman!

![](images/property-photo-upload-postman.png)

**Notice**. By default, Spatie Media Library package will store the files in your `public` disk, which is in `storage/app/public` according to Laravel default standard. You can customize both in `config/filesystems.php` and/or `config/medialibrary.php` files.

**Notice 2**. For your `storage/app/public` folder to be actually visible in public in the browser, you need to run the command `php artisan storage:link`.

The final thing for the file upload is to build a thumbnail image for the original file, cause probably we would like to show a smaller version of the photo if the initial file is quite large.

For that, Spatie Media Library has a concept of **Media Conversions**, you just need to define them in the Model, as one method.

**app/Models/Property.php**:
```php
class Property extends Model implements HasMedia
{
	// ...

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(800);
    }
}
```

And that's it: whenever we add a new file to the Media Collection, it will try to build a thumbnail with 800px width, automatically.

Let's actually return that one, too, in the Controller.

**app/Http/Controllers/Owner/PropertyPhotoController.php**:
```php
public function store(Property $property, Request $request)
{
    // ...

    $photo = $property->addMediaFromRequest('photo')->toMediaCollection('photos');

    return [
        'filename' => $photo->getUrl(),
        'thumbnail' => $photo->getUrl('thumbnail')
    ];
}
```

And here's the updated result in Postman:

![](images/property-photo-upload-thumbnail.png)

Now, let's write an automated test for this, I will put it as a new method to already existing test file of `PropertiesTest`. 

To test the file uploads, we need to call `Storage::fake();` in the beginning of the method, here's the full code of that test method.

**tests/Feature/PropertiesTest.php**:
```php
// ...

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertiesTest extends TestCase
{
    // ... other methods

    public function test_property_owner_can_add_photo_to_property()
    {
        Storage::fake();

        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
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
}
```

As you can see, we're creating a property, then post a JSON request with a fake file via `UploadedFile::fake()->image()` and then assert the results to be returned correctly.

From my testing, I've noticed that Media Library builds the thumbnails as `.jpg` files, even if the original is `.png`, so we assert exactly that.

---