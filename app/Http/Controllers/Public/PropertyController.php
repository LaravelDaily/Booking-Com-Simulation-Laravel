<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertySearchResource;
use App\Models\Property;
use Illuminate\Http\Request;

/**
 * @group Public
 * @subgroup Property
 */
class PropertyController extends Controller
{
    /**
     * Property details
     *
     * [Returns details of a property]
     *
     * @response {"properties":{"data":[{"id":1,"name":"Aspernatur nostrum.","address":"5716 Leann Point, 24974-6081, New York","lat":"8.8008940","long":"-82.9095500","apartments":[{"name":"Mid size apartment","type":null,"size":null,"beds_list":"","bathrooms":0,"price":0}],"photos":[],"avg_rating":null}],"links":{"first":"http:\/\/booking-com-simulation-laravel.test\/api\/search?city=1&adults=2&children=1&page=1","last":"http:\/\/booking-com-simulation-laravel.test\/api\/search?city=1&adults=2&children=1&page=1","prev":null,"next":null},"meta":{"current_page":1,"from":1,"last_page":1,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http:\/\/booking-com-simulation-laravel.test\/api\/search?city=1&adults=2&children=1&page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"path":"http:\/\/booking-com-simulation-laravel.test\/api\/search","per_page":10,"to":1,"total":1}},"facilities":[]}
     */
    public function __invoke(Property $property, Request $request)
    {
        $property->load('apartments.facilities');

        if ($request->adults && $request->children) {
            $property->load(['apartments' => function ($query) use ($request) {
                $query->where('capacity_adults', '>=', $request->adults)
                    ->where('capacity_children', '>=', $request->children)
                    ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                        $query->whereDoesntHave('bookings', function ($q) use ($request) {
                            $q->validForRange([$request->start_date, $request->end_date]);
                        });
                    })
                    ->orderBy('capacity_adults')
                    ->orderBy('capacity_children');
            }, 'apartments.facilities']);
        }

        return new PropertySearchResource($property);
    }
}
