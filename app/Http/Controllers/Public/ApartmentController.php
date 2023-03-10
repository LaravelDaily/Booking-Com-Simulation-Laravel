<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentDetailsResource;
use App\Models\Apartment;

class ApartmentController extends Controller
{
    public function __invoke(Apartment $apartment)
    {
        $apartment->load('facilities.category');

        $apartment->setAttribute(
            'facility_categories',
            $apartment->facilities->groupBy('category.name')->mapWithKeys(fn ($items, $key) => [$key => $items->pluck('name')])
        );

        return new ApartmentDetailsResource($apartment);
    }
}
