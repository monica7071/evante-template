<?php

namespace App\Http\Controllers;

use App\Models\Listing;

class PublicListingController extends Controller
{
    public function show(Listing $unit)
    {
        $unit->load(['listingImages', 'project', 'location']);

        return view('listings.units.public-show', compact('unit'));
    }
}
