<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\StoreBookingRequest;
use App\Http\Requests\Api\V2\UpdateBookingRequest;
use App\Http\Resources\Api\V2\BookingResource;
use App\Jobs\UpdatePropertyRatingJob;
use App\Models\Booking;

class BookingController extends Controller
{
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

    public function store(StoreBookingRequest $request)
    {
        $booking = auth()->user()->bookings()->create($request->validated());

        $booking->guests()->createMany($request->validated('guests'));

        $booking->load(['guests']);

        return new BookingResource($booking);
    }

    public function show(Booking $booking)
    {
        $this->authorize('bookings-manage');

        if ($booking->user_id != auth()->id()) {
            abort(403);
        }

        $booking->load(['guests']);

        return new BookingResource($booking);
    }

    public function update(Booking $booking, UpdateBookingRequest $request)
    {
        if ($booking->user_id != auth()->id()) {
            abort(403);
        }

        $booking->update($request->validated());

        dispatch(new UpdatePropertyRatingJob($booking));

        return new BookingResource($booking);
    }

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
