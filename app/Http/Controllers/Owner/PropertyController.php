<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;

/**
 * @group Owner
 * @subgroup Property management
 */
class PropertyController extends Controller
{
    /**
     * Properties list - **INCOMPLETE**
     *
     * [List of owners properties]
     *
     * @authenticated
     *
     * @response {"success": true}
     *
     */
    public function index()
    {
        $this->authorize('properties-manage');

        // Will implement property management later
        return response()->json(['success' => true]);
    }

    /**
     * Store property
     *
     * [Stores new property of the owner]
     *
     * @authenticated
     *
     * @response {"name":"My property","city_id":1,"address_street":"Street Address 1","address_postcode":"12345","owner_id":2,"updated_at":"2023-05-10T07:07:45.000000Z","created_at":"2023-05-10T07:07:45.000000Z","id":1,"city":{"id":1,"country_id":1,"name":"New York","lat":"40.7127760","long":"-74.0059740","created_at":"2023-05-10T07:07:45.000000Z","updated_at":"2023-05-10T07:07:45.000000Z","country":{"id":1,"name":"United States","lat":"37.0902400","long":"-95.7128910","created_at":"2023-05-10T07:07:45.000000Z","updated_at":"2023-05-10T07:07:45.000000Z"}}}
     */
    public function store(StorePropertyRequest $request)
    {
        $this->authorize('properties-manage');

        return Property::create($request->validated());
    }
}
