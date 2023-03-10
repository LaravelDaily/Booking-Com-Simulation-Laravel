Now, let's move to the detailed view of the specific Apartment which would show facilities grouped by category.

Again, from the previous lesson, here's how it looks on mobile:

![property show mobile facilities](images/property-show-mobile-facilities.png)

For that, we create a new Controller.

```sh
php artisan make:controller Public/ApartmentController
```

Then we add it to the routes:

**routes/api.php**:
```php
Route::get('search',
    \App\Http\Controllers\Public\PropertySearchController::class);
Route::get('properties/{property}',
    \App\Http\Controllers\Public\PropertyController::class);
Route::get('apartments/{apartment}',
    \App\Http\Controllers\Public\ApartmentController::class);
```

But wait, we have the third public route and Controller, too much repeating code. Time to move those Controller namespaces into the `use` section. Did you know you can do something like this?

**routes/api.php**:
```php
use App\Http\Controllers\Public;

Route::get('search', Public\PropertySearchController::class);
Route::get('properties/{property}', Public\PropertyController::class);
Route::get('apartments/{apartment}', Public\ApartmentController::class);
```

So, not `use` a specific Controller, but the whole namespace instead. Cool, right?

Now, inside the Controller, we could simply do something like this, reusing the same resource as in search:

```php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentSearchResource;
use App\Models\Apartment;

class ApartmentController extends Controller
{
    public function __invoke(Apartment $apartment)
    {
        $apartment->load('facilities.category');

        return new ApartmentSearchResource($apartment);
    }
}
```

But the problem is that we need **different** structure to be returned, with facilities grouped into categories. So, we generate another API resource - for the same Apartment model, but for different purpose.

```sh
php artisan make:resource ApartmentDetailsResource
```

**app/Http/Resources/ApartmentDetailsResource.php**:
```php
namespace App\Http\Resources;
class ApartmentDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'type' => $this->apartment_type?->name,
            'size' => $this->size,
            'beds_list' => $this->beds_list,
            'bathrooms' => $this->bathrooms,
            'facility_categories' => $this->facility_categories,
        ];
    }
}
```

See the `facility_categories`? How do we populate that? Here's one of the options:

```php
class ApartmentController extends Controller
{
    public function __invoke(Apartment $apartment)
    {
        $apartment->load('facilities.category');
        $facilityCategories = $apartment->facilities
            ->groupBy('category.name');

        $categories = [];
        foreach ($facilityCategories as $category => $facilities) {
            $facilityNames = [];
            foreach ($facilities as $facility) {
                $facilityNames[] = $facility->name;
            }
            $categories[$category] = $facilityNames;
        }
        $apartment->facility_categories = $categories;

        return new ApartmentDetailsResource($apartment);
    }
}
```

