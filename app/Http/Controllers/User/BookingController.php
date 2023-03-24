<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $this->authorize('bookings-manage');

        // Will implement booking management later
        return response()->json(['success' => true]);
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = auth()->user()->bookings()->create($request->validated());
        $booking->load('apartment.property');

        return new BookingResource($booking);
    }
}
