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
        $facilityCategories = $apartment->facilities
            ->groupBy('category.name');

        $categories = [];
        foreach ($facilityCategories as $category => $facilities) {
            $facilityNames = [];
            foreach ($facilities as $facility) {
                $facilityNames[] = $facility->name;
            }
            $categories[$category] = $facilityNames;
        }
        $apartment->facility_categories = $categories;

        return new ApartmentDetailsResource($apartment);
    }
}
