<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function __invoke(Request $request)
    {
        return Property::with('city')
            ->when($request->city, function($query) use ($request) {
                $query->where('city_id', $request->city);
            })
            ->when($request->country, function($query) use ($request) {
                $query->whereHas('city', fn($q) => $q->where('country_id', $request->country));
            })
            ->when($request->lat && $request->long, function($query) use ($request) {
                $haversine = "(
                    6371 * acos(
                        cos(radians(" .$request->lat. "))
                        * cos(radians(`lat`))
                        * cos(radians(`long`) - radians(" .$request->long. "))
                        + sin(radians(" .$request->lat. ")) * sin(radians(`long`))
                    ) < 10
                )";
                $query->whereRaw($haversine);
            })
            ->get();
    }
}
