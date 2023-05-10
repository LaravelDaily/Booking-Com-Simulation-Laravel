<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Jobs\UpdatePropertyRatingJob;
use App\Models\Booking;

/**
 * @group User
 * @subgroup Bookings
 */
class BookingController extends Controller
{
    /**
     * List of user bookings
     *
     * [Returns preview list of all user bookings]
     *
     * @authenticated
     *
     * @response {"id":1,"apartment_name":"Fugiat saepe sed.: Apartment","start_date":"2023-05-11","end_date":"2023-05-12","guests_adults":1,"guests_children":0,"total_price":0,"cancelled_at":null,"rating":null,"review_comment":null}
     */
    public function index()
    {
        $this->authorize('bookings-manage');

        $bookings = auth()->user()->bookings()
            ->with('apartment.property')
            ->withTrashed()
            ->orderBy('start_date')
            ->get();
        return BookingResource::collection($bookings);
    }

    /**
     * Create new booking
     *
     * [Creates new booking for authenticated user]
     *
     * @authenticated
     *
     * @response 201 {"id":1,"apartment_name":"Hic consequatur qui.: Apartment","start_date":"2023-05-11 08:00:51","end_date":"2023-05-12 08:00:51","guests_adults":2,"guests_children":1,"total_price":0,"cancelled_at":null,"rating":null,"review_comment":null}
     */
    public function store(StoreBookingRequest $request)
    {
        $booking = auth()->user()->bookings()->create($request->validated());

        return new BookingResource($booking);
    }

    /**
     * View booking
     *
     * [Returns details about a booking]
     *
     * @authenticated
     *
     * @response {"id":1,"apartment_name":"Hic consequatur qui.: Apartment","start_date":"2023-05-11 08:00:51","end_date":"2023-05-12 08:00:51","guests_adults":2,"guests_children":1,"total_price":0,"cancelled_at":null,"rating":null,"review_comment":null}
     */
    public function show(Booking $booking)
    {
        $this->authorize('bookings-manage');

        if ($booking->user_id != auth()->id()) {
            abort(403);
        }

        return new BookingResource($booking);
    }

    /**
     * Update existing booking rating
     *
     * [Updates booking with new details]
     *
     * @authenticated
     *
     * @response {"id":1,"apartment_name":"Hic consequatur qui.: Apartment","start_date":"2023-05-11 08:00:51","end_date":"2023-05-12 08:00:51","guests_adults":2,"guests_children":1,"total_price":0,"cancelled_at":null,"rating":null,"review_comment":null}
     */
    public function update(Booking $booking, UpdateBookingRequest $request)
    {
        if ($booking->user_id != auth()->id()) {
            abort(403);
        }

        $booking->update($request->validated());

        dispatch(new UpdatePropertyRatingJob($booking));

        return new BookingResource($booking);
    }

    /**
     * Delete booking
     *
     * [Deletes a booking]
     *
     * @authenticated
     *
     * @response {}
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('bookings-manage');

        if ($booking->user_id != auth()->id()) {
            abort(403);
        }

        $booking->delete();

        return response()->noContent();
    }
}
