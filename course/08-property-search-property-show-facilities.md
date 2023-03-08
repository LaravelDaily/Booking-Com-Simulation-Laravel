Now imagine the user clicks on the property to choose the apartment. Inside of the property view, we need to show the full list of available apartments, with their **facilities**:

![Property show facilities](images/property-show-facilities.png)

The **Facility** model will be a new top in this lesson. Here's the plan: 

- First, we will build Facility DB/relationship structure
- Then, show facilities in the new Property endpoint
- Then add the facilities into search filters
- Of course, all covered by tests

Let's go! 

---

## Facilities: DB Structure

An obvious question: what are those facilities, what are the different options and categories for them! There could be dozens or even hundreds of various facilities.

I've found the answer to this question while browsing the **mobile** version of Booking.com, which showed the apartment facilities in a well-structured way:

![property show mobile facilities](images/property-show-mobile-facilities.png)

So I imagine structure something like this:

**facility_categories**:

- id
- name


**facilities**

- id
- category_id
- name


**apartment_facility**

- apartment_id
- facility_id


Let's create exactly that.

```sh
php artisan make:model FacilityCategory -ms
```

Notice that I generate the model with **seeder** this time, with a flag `-s`.

**Migration file**:
```php
public function up(): void
{
    Schema::create('facility_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}
```

**app/Models/FacilityCategory.php**:
```php
class FacilityCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
```

And let's add the categories for the seeder. This time, different from other lessons, I will do that in a separate seeder, cause that list is likely to change in the future, not so static.

**database/seeders/FacilityCategorySeeder.php**:
```php
namespace Database\Seeders;

use App\Models\FacilityCategory;

class FacilityCategorySeeder extends Seeder
{
    public function run(): void
    {
        FacilityCategory::create(['name' => 'Bedroom']);
        FacilityCategory::create(['name' => 'Kitchen']);
        FacilityCategory::create(['name' => 'Bathroom']);
        FacilityCategory::create(['name' => 'Room Amenities']);
        FacilityCategory::create(['name' => 'General']);
        FacilityCategory::create(['name' => 'Media & Technology']);
    }
}
```

Same with Facilities.

```sh
php artisan make:model Facility -ms
```

**Migration file**:
```php
public function up(): void
{
    Schema::create('facilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')
            ->nullable()
            ->references('id')
            ->on('facility_categories');
        $table->string('name');
        $table->timestamps();
    });
}
```

Notice that I specify `nullable()` this time, just in case - maybe there will be separate facility without categories.

Also, instead of `constrained()`, I specify `references()->on()`, because the table name is not `categories`, as the default `facilities.category_id` field name would expect.

**app/Models/Facility.php**:
```php
class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(FacilityCategory::class, 'category_id');
    }
}
```

Also, I think it's logical to create a `hasMany()` relationship right away, as I feel we will use it in the lists.

**app/Models/FacilityCategory.php**:
```php
class FacilityCategory extends Model
{
    // ...

    public function facilities()
    {
        return $this->hasMany(Facility::class, 'category_id');
    }
}
```

Similarly, let's seed a few facilities, I will take them from the same mobile screenshot above.

**database/seeders/FacilitySeeder.php**:
```php
use App\Models\Facility;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        Facility::create(['category_id' => 1, 'name' => 'Linen']);
        Facility::create(['category_id' => 1, 'name' => 'Wardrobe or closet']);
        Facility::create(['category_id' => 2, 'name' => 'Electric kettle']);
        Facility::create(['category_id' => 2, 'name' => 'Microwave']);
        Facility::create(['category_id' => 2, 'name' => 'Washing mashine']);
        Facility::create(['category_id' => 3, 'name' => 'Private bathroom']);
        Facility::create(['category_id' => 3, 'name' => 'Shower']);
        Facility::create(['category_id' => 3, 'name' => 'Towels']);
        Facility::create(['category_id' => 4, 'name' => 'Drying rack for clothing']);
        Facility::create(['category_id' => 5, 'name' => 'No smoking']);
        Facility::create(['category_id' => 5, 'name' => 'Fan']);
        Facility::create(['category_id' => 6, 'name' => 'WiFi']);
        Facility::create(['category_id' => 6, 'name' => 'TV']);
    }
}
```

Finally, I add both new Seeder files into the main one.

**database/seeders/DatabaseSeeder.php**:
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

        $this->call(FacilityCategorySeeder::class);
        $this->call(FacilitySeeder::class);
    }
}
```

So, we have the structure for the facilities and categories, now let's attach them to the **Apartments**, with a many-to-many pivot table.

```sh
php artisan make:migration create_apartment_facility_table
```

**Migration file**:
```
public function up(): void
{
    Schema::create('apartment_facility', function (Blueprint $table) {
        $table->foreignId('apartment_id')->constrained();
        $table->foreignId('facility_id')->constrained();
    });
}
```

And let's add a relationship: for now, from the apartment side.

**app/Models/Apartment.php**:
```php
class Apartment extends Model
{
    // ...

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }
}
```

Ok great, now we have the data. Next, how do we **show** that data?

---

## Property: Show Apartments with Facilities

Let's get back to the initial screenshot from Booking.com, in the beginning of this lesson. I will repeat it here.

![Property show facilities](images/property-show-facilities.png)

This is the page that you get after clicking on a specific property from search result. We don't have that endpoint yet, so let's create it.

It will be a new controller, a public one, to show properties.

