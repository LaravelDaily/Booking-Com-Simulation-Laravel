<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertySearchResource;
use App\Models\Facility;
use App\Models\Geoobject;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertySearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $properties = Property::query()
            ->with([
                'city',
                'apartments.apartment_type',
                'apartments.rooms.beds.bed_type',
                'facilities',
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
                        ->orderBy('capacity_adults')
                        ->orderBy('capacity_children')
                        ->take(1);
                });
            })
            ->get();

        $facilities = Facility::query()
            ->withCount(['properties' => function ($property) use ($properties) {
                $property->whereIn('id', $properties->pluck('id'));
            }])
            ->get()
            ->where('properties_count', '>', 0)
            ->sortByDesc('properties_count')
            ->pluck('properties_count', 'name');

        return [
            'properties' => PropertySearchResource::collection($properties),
            'facilities' => $facilities,
        ];
    }
}
