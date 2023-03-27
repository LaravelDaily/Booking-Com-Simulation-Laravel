We're finally at the point when we can **make bookings**. First, the database structure for that.

---

## Booking DB Model/Migration

```sh
php artisan make:model Booking -m
```

**Migration file**:
```php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('apartment_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->date('start_date');
    $table->date('end_date');
    $table->unsignedInteger('guests_adults');
    $table->unsignedInteger('guests_children');
    $table->unsignedInteger('total_price');
    $table->timestamps();
    $table->softDeletes();
});
```

A few things to notice here:

- Foreign keys for which apartment is booked and which user is booking
- The field `total_price` will be automatically calculated with Observers
- I decided to use Soft Deletes for canceling bookings

We'll get to all of those things later in this lesson. For now, the Model.

**app/Models/Booking.php**:
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'apartment_id',
        'user_id',
        'start_date',
        'end_date',
        'guests_adults',
        'guests_children',
        'total_price'
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
```

Also, let's create a migration from User and Apartment models. I have a feeling we will use both.

**app/Models/User.php**:
```php
public function bookings()
{
    return $this->hasMany(Booking::class);
}
```

**app/Models/Apartment.php**:
```php
public function bookings()
{
    return $this->hasMany(Booking::class);
}
```

Next, the Route for the endpoint.

---

## API Endpoint & First Successful Booking

In fact, we already have the `User/BookingController.php`, but we used it only for testing the permissions in the very beginning of the course.

So now, instead of just one `Route::get()`, let's transform it into a proper Resourceful Controller.

**routes/web.php**:
```php
Route::prefix('user')->group(function () {
    Route::resource('bookings', \App\Http\Controllers\User\BookingController::class);
});
```

Next, the `store()` method. But before we fill that in, I suggest we generate two things - a Form Request class, and an API Resource.

```sh
php artisan make:request StoreBookingRequest
php artisan make:resource BookingResource
```

Now, we can use those classes (empty for now) in the Controller:

**app/Http/Controllers/User/BookingController.php**:
```php
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;

// ...

public function store(StoreBookingRequest $request)
{
    $booking = auth()->user()->bookings()->create($request->validated());

    return new BookingResource($booking);
}
```

Now, let's fill in the validation FormRequest with the rules.

**app/Http/Requests/StoreBookingRequest.php**:
```php
use Illuminate\Support\Facades\Gate;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('bookings-manage');
    }

    public function rules(): array
    {
        return [
            'apartment_id' => ['required', 'exists:apartments,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'guests_adults' => ['integer'],
            'guests_children' => ['integer'],
        ];
    }
}
```

As you can see, in the case of Form Request, we use Gates on that level, instead of checking it in the Controller. It's just my personal preference, no particular reason.

Also, we're checking if the apartment exists.

We will add a few more complicated validation rules a bit later, now let's return our result, which is an object of a new booking.

Here's the structure that I suggest.

**app/Http/Resources/BookingResource.php**:
```php
class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'apartment_name' => $this->apartment->property->name . ': ' . $this->apartment->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'guests_adults' => $this->guests_adults,
            'guests_children' => $this->guests_children,
            'total_price' => $this->total_price,
            'cancelled_at' => $this->deleted_at?->toDateString(),
        ];
    }
}
```

And when we launch `POST api/user/bookings` with the correct user's Bearer Token in Postman...

![](images/booking-post-postman.png)

Looks great, it works!

---

## Calculating Total Price

[TODO]

But wait, we need to validate a few more things.

---

## Validate Apartment Capacity and Availability

I suggest we add two more validations:

- If `guests_adults/guests_children` still first the apartment capacity
- If someone else hadn't booked the apartment already, while we were browsing around

We can fit those both in a Custom Validation Rule, let's call it `ApartmentAvailableRule`.