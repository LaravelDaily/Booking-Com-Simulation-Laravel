<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyPhotoController extends Controller
{
    public function store(Property $property, Request $request)
    {
        $property->addMediaFromRequest('photo')->toMediaCollection('photos');

        return $property;
    }
}
