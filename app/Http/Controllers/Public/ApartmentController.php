<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentDetailsResource;
use App\Models\Apartment;

/**
 * @group Public
 * @subgroup Apartments
 */
class ApartmentController extends Controller
{
    /**
     * Get apartment details
     *
     * [Returns details about a specific apartment]
     *
     * @response {"name":"Large apartment","type":null,"size":null,"beds_list":"","bathrooms":0,"facility_categories":{"First category":["First facility","Second facility"],"Second category":["Third facility"]}}
     */
    public function __invoke(Apartment $apartment)
    {
        $apartment->load('facilities.category');

        $apartment->setAttribute(
            'facility_categories',
            $apartment->facilities->groupBy('category.name')->mapWithKeys(fn($items, $key) => [$key => $items->pluck('name')])
        );

        return new ApartmentDetailsResource($apartment);
    }
}
