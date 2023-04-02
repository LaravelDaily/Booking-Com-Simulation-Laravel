The next thing we'll work on is adding **real estate properties**: houses/homes to rent. In this particular lesson, we will focus on adding the **geographical data** for city, country, and latitude/longitude.

---

## Goals of This Lesson

- Build a DB schema for countries, cities, and geographical objects, seeding a few of each
- Build a first version of DB schema for properties, with geographical data
- Automatically set property latitude/longitude based on the address, with Observer and Google Maps API
- First version of API endpoint to create a property, covered by PHPUnit test

By the end of this lesson, we will see this in Postman:

![](images/property-create.png)

---

## Geo Coordinates: Countries / Cities / Famous Objects

First, let's add the latitude and longitude columns to the DB table of `countries`.

```sh
php artisan make:migration add_geocoordinates_to_countries_table
```

**Migration file**:
```php
Schema::table('countries', function (Blueprint $table) {
    $table->after('name', function() use ($table) {
        $table->decimal('lat', 10, 7)->nullable();
        $table->decimal('long', 10, 7)->nullable();
    });
});
```

Next, we will definitely build a search by **city**, so we need a model for that as well, with coordinates.

```sh
php artisan make:model City -ms
```

**app/Models/City.php**:
```php
class City extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'name', 'lat', 'long'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
```

**Migration file**:
```php
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('country_id')->constrained();
    $table->string('name');
    $table->decimal('lat', 10, 7)->nullable();
    $table->decimal('long', 10, 7)->nullable();
    $table->timestamps();
});
```

Finally, let's create a separate database table for **geographical objects**, such as "Big Ben" or "Statue of Liberty", cause people often search by them. 

![Search by geoobject](images/search-by-geoobject.png)

```sh
php artisan make:model Geoobject -ms
```

**app/Models/Geoobject.php**:
```php
class Geoobject extends Model
{
    use HasFactory;

    protected $fillable = ['city_id', 'name', 'lat', 'long'];
}
```

**Migration file**:
```php
Schema::create('geoobjects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('city_id')->nullable()->constrained();
    $table->string('name');
    $table->decimal('lat', 10, 7)->nullable();
    $table->decimal('long', 10, 7)->nullable();
    $table->timestamps();
});
```

Next, let's build the Seeders for all those new tables. We will use them to automatically run seeds in our automated tests, too. So, we fill in a few countries, a few cities, and a few geographical objects.

```sh
php artisan make:seeder CitySeeder
php artisan make:seeder GeoobjectSeeder
```

Country seeder already existed, we just need to fill it with data.

**database/seeders/CountrySeeder.php**:
```php
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run()
    {
        Country::create([
            'name' => 'United States', 
            'lat' => 37.09024, 
            'long' => -95.712891
        ]);
        Country::create([
            'name' => 'United Kingdom', 
            'lat' => 55.378051, 
            'long' => -3.435973
        ]);
    }
}
```

**database/seeders/CitySeeder.php**:
```php
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run()
    {
        City::create([
            'country_id' => 1,
            'name' => 'New York',
            'lat' => 40.712776,
            'long' => -74.005974,
        ]);

        City::create([
            'country_id' => 2,
            'name' => 'London',
            'lat' => 51.507351,
            'long' => -0.127758,
        ]);
    }
}
```

**database/seeders/GeoobjectSeeder.php**:
```php
use App\Models\Geoobject;

class GeoobjectSeeder extends Seeder
{
    public function run()
    {
        Geoobject::create([
            'city_id' => 1,
            'name' => 'Statue of Liberty',
            'lat' => 40.689247,
            'long' => -74.044502
        ]);

        Geoobject::create([
            'city_id' => 2,
            'name' => 'Big Ben',
            'lat' => 51.500729,
            'long' => -0.124625
        ]);
    }
}
```

Then we, of course, add them all to the main DatabaseSeeder, which now will look like this:

```php
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(PermissionSeeder::class);

        $this->call(CountrySeeder::class);
        $this->call(CitySeeder::class);
        $this->call(GeoobjectSeeder::class);
    }
}
```

Great, now we have some geographical entities to play around with, now let's go to the actual properties!

---

## Properties DB Table with Auto-Coordinates

We create a model/migration/seeder for our "main" table of the project:

```sh
php artisan make:model Property -ms
```

And here's the schema with the main fields, for now. There will be more, but at the moment, we focus on **geographical** things for the search, remember?

```php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users');
    $table->string('name');
    $table->foreignId('city_id')->constrained();
    $table->string('address_street');
    $table->string('address_postcode')->nullable();
    $table->decimal('lat', 10, 7)->nullable();
    $table->decimal('long', 10, 7)->nullable();
    $table->timestamps();
});
```

And the model should contain those fields as fillable.

**app/Models/Property.php**:
```php
class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'city_id',
        'address_street',
        'address_postcode',
        'lat',
        'long',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
```

Now, when someone enters a new property, wouldn't it be nice if lat/long fields would be automatically populated, by street address?

I have a [separate tutorial](https://laraveldaily.com/post/laravel-get-latitude-longitude-from-address-geocoder) on how to do that with Google Maps API (*warning: API is not free!*), and will make a re-cap of it here in this lesson.

We will use a package called [GeocoderLaravel](https://github.com/geocoder-php/GeocoderLaravel) that allows you to easily integrate Google Maps API in your Laravel project.

```sh
composer require toin0u/geocoder-laravel
php artisan vendor:publish --provider="Geocoder\Laravel\Providers\GeocoderService"
```

Then, we add the Google Maps API key (read [here](https://developers.google.com/maps/documentation/embed/get-api-key) how to get it) to the `.env` file:

```sh
GOOGLE_MAPS_API_KEY=AIzaSyAWRsRGOFbTXRlLHDOSudkerLjUtBfElUt
```

And then, we just need to call these lines to convert the address into coordinates:

```php
$result = app('geocoder')->geocode($address)->get();
$coordinates = $result[0]->getCoordinates();
$lat = $coordinates->getLatitude();
$long = $coordinates->getLongitude();
```

To automate all this process, we create an **Observer** file, to watch for the creation of the new Properties.

```sh
php artisan make:observer PropertyObserver --model=Property
```

**app/Observers/PropertyObserver.php**:
```php
use App\Models\Property;

class PropertyObserver
{
    public function creating(Property $property)
    {
        // We also add the owner automatically
        if (auth()->check()) {
            $property->owner_id = auth()->id();
        }

        if (is_null($property->lat) && is_null($property->long)) {
            $fullAddress = $property->address_street . ', '
                . $property->address_postcode . ', '
                . $property->city->name . ', '
                . $property->city->country->name;
            $result = app('geocoder')->geocode($fullAddress)->get();
            if ($result->isNotEmpty()) {
                $coordinates = $result[0]->getCoordinates();
                $property->lat = $coordinates->getLatitude();
                $property->long = $coordinates->getLongitude();
            }
        }
    }
}
```

Finally, we register that Observer, let's do it directly in the Model.

**app/Models/Property.php**:
```php
use App\Observers\PropertyObserver;

class Property extends Model
{
    // ...

    public static function booted()
    {
        parent::booted();

        self::observe(PropertyObserver::class);
    }
}
```

Great, now whenever someone creates a new Property, coordinates should be auto-filled.

---

## Creating Property: Route/Controller/Request

Now, let's build a Controller/Route to create a new property, and add a Form Request immediately, too.

```sh
php artisan make:controller Owner/PropertyController
php artisan make:request StorePropertyRequest
```

**app/Http/Controllers/Owner/PropertyController.php**:
```php
namespace App\Http\Controllers\Owner;

use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;

class PropertyController extends Controller
{
    // ...

    public function store(StorePropertyRequest $request)
    {
        $this->authorize('properties-manage');

        return Property::create($request->validated());
    }
}
```

**app/Http/Requests/StorePropertyRequest.php**:
```php
class StorePropertyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'city_id' => 'required|exists:cities,id',
            'address_street' => 'required',
            'address_postcode' => 'required',
        ];
    }
}
```

As you can see, we require only the name/city/address, as owner/lat/long will be filled automatically by the Observer.

Finally, the new route:

```php
Route::middleware('auth:sanctum')->group(function () {
    // ...

    Route::post('owner/properties',
        [\App\Http\Controllers\Owner\PropertyController::class, 'store']);

});
```

We try it out in Postman:

![Property Create](images/property-create.png)

Finally, in this lesson, let's add the automatic test that it actually works.

**tests/Feature/PropertiesTest.php**:
```php
namespace Tests\Feature;

class PropertiesTest extends TestCase
{
    // ...

    public function test_property_owner_can_add_property()
    {
        $owner = User::factory()->create(['role_id' => Role::ROLE_OWNER]);
        $response = $this->actingAs($owner)->postJson('/api/owner/properties', [
            'name' => 'My property',
            'city_id' => City::value('id'),
            'address_street' => 'Street Address 1',
            'address_postcode' => '12345',
        ]);

        $response->assertSuccessful();
        $response->assertJsonFragment(['name' => 'My property']);
    }
}
```

A debatable question is whether we should leave the auto-coordinates enabled while testing. Probably not, as we don't want to get charged for Google API every time we run automated tests, right?

So, this is how I disable that part of the Observer:

**app/Observers/PropertyObserver.php**:
```php
class PropertyObserver
{
    public function creating(Property $property)
    {
        if (is_null($property->lat) && is_null($property->long) && !(app()->environment('testing'))) {

            // ... getting the coordinates

        }
    }
}
```

In other words, while testing, if we don't provide the lat/long for the new property, the coordinates will remain NULL, which is fine.

![Property Create Test](images/property-create-test.png)

We may create another **Unit** test that will assert that the API works or Mock/Fake data, but this is outside of the scope of this course. For that, please read the tutorial [Laravel Testing: Mocking/Faking External 3rd Party APIs
](https://laraveldaily.com/post/laravel-testing-mocking-faking-external-api).
