<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;

class PropertyController extends Controller
{
    public function index()
    {
        $this->authorize('properties-manage');

        // Will implement property management later
        return response()->json(['success' => true]);
    }
}
