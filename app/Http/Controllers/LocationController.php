<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::withCount('projects');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('subdistrict', 'like', "%{$search}%");
            });
        }

        $locations = $query->latest()->paginate(15)->withQueryString();

        return view('listings.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('listings.locations.create', [
            'location' => new Location(),
            'provinceOptions' => $this->loadThaiData('provinces', 'provinces'),
            'districtOptions' => $this->loadThaiData('districts', 'districts'),
            'subDistrictOptions' => $this->loadThaiData('sub_districts', 'sub_districts'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'subdistrict' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function show(Location $location)
    {
        $location->loadCount('projects');

        return view('listings.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        return view('listings.locations.edit', [
            'location' => $location,
            'provinceOptions' => $this->loadThaiData('provinces', 'provinces'),
            'districtOptions' => $this->loadThaiData('districts', 'districts'),
            'subDistrictOptions' => $this->loadThaiData('sub_districts', 'sub_districts'),
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'subdistrict' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }

    private function loadThaiData(string $filename, string $key): array
    {
        $path = public_path("data/thai/{$filename}.json");
        if (!File::exists($path)) {
            return [];
        }

        $json = json_decode(File::get($path), true);

        return $json[$key] ?? [];
    }
}
