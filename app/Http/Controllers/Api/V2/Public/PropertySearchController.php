<?php

namespace App\Http\Controllers\Api\V2\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\PropertySearchResource;
use App\Models\Facility;
use App\Models\Geoobject;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $propertiesQuery = Property::query()
            ->with([
                'city',
                'apartments.apartment_type',
                'apartments.beds.bed_type',
                'apartments.prices' => function($query) use ($request) {
                    $query->validForRange([
                        $request->start_date ?? now()->addDay()->toDateString(),
                        $request->end_date ?? now()->addDays(2)->toDateString(),
                    ]);
                },
                'facilities',
                'media' => fn($query) => $query->orderBy('position'),
            ])
            ->when($request->city, function($query) use ($request) {
                $query->where('city_id', $request->city);
            })
            ->when($request->country, function($query) use ($request) {
                $query->whereHas('city', fn($q) => $q->where('country_id', $request->country));
            })
            ->when($request->geoobject, function($query) use ($request) {
                $geoobject = Geoobject::find($request->geoobject);
                if ($geoobject) {
                    $condition = "(
                        6371 * acos(
                            cos(radians(" . $geoobject->lat . "))
                            * cos(radians(`lat`))
                            * cos(radians(`long`) - radians(" . $geoobject->long . "))
                            + sin(radians(" . $geoobject->lat . ")) * sin(radians(`lat`))
                        ) < 10
                    )";
                    $query->whereRaw($condition);
                }
            })
            ->when($request->adults && $request->children, function($query) use ($request) {
                $query->withWhereHas('apartments', function($query) use ($request) {
                    $query->where('capacity_adults', '>=', $request->adults)
                        ->where('capacity_children', '>=', $request->children)
                        ->when($request->start_date && $request->end_date, function($query) use ($request) {
                            $query->whereDoesntHave('bookings', function($q) use ($request) {
                                $q->validForRange([$request->start_date, $request->end_date]);
                            });
                        })
                        ->orderBy('capacity_adults')
                        ->orderBy('capacity_children')
                        ->take(1);
                });
            })
            ->when($request->facilities, function($query) use ($request) {
                $query->whereHas('facilities', function($query) use ($request) {
                    $query->whereIn('facilities.id', $request->facilities);
                });
            })
            ->when($request->price_from, function ($query) use ($request) {
                $query->whereHas('apartments.prices', function ($query) use ($request) {
                    $query->where('price', '>=', $request->price_from);
                });
            })
            ->when($request->price_to, function ($query) use ($request) {
                $query->whereHas('apartments.prices', function ($query) use ($request) {
                    $query->where('price', '<=', $request->price_to);
                });
            })
            ->when($request->wheelchair_access, function ($query) use ($request) {
                $query->whereHas('apartments', function ($query) use ($request) {
                    $query->where('wheelchair_access', $request->wheelchair_access);
                });
            })
            ->when($request->pets_allowed, function ($query) use ($request) {
                $query->whereHas('apartments', function ($query) use ($request) {
                    $query->where('pets_allowed', $request->pets_allowed);
                });
            })
            ->when($request->smoking_allowed, function ($query) use ($request) {
                $query->whereHas('apartments', function ($query) use ($request) {
                    $query->where('smoking_allowed', $request->smoking_allowed);
                });
            })
            ->when($request->free_cancellation, function ($query) use ($request) {
                $query->whereHas('apartments', function ($query) use ($request) {
                    $query->where('free_cancellation', $request->free_cancellation);
                });
            })
            ->when($request->all_day_access, function ($query) use ($request) {
                $query->whereHas('apartments', function ($query) use ($request) {
                    $query->where('all_day_access', $request->all_day_access);
                });
            });

        $facilities = Facility::query()
            ->withCount(['properties' => function ($property) use ($propertiesQuery) {
                $property->whereIn('id', $propertiesQuery->pluck('id'));
            }])
            ->get()
            ->where('properties_count', '>', 0)
            ->sortByDesc('properties_count')
            ->pluck('properties_count', 'name');

        $properties = $propertiesQuery
            ->orderBy('bookings_avg_rating', 'desc')
            ->paginate(10)
            ->withQueryString();

        return [
            'properties' => PropertySearchResource::collection($properties)
                ->response()
                ->getData(true),
            'facilities' => $facilities,
        ];
    }
}
