<?php

namespace App\Http\Controllers;

use App\Exports\ListingTemplateExport;
use App\Imports\ListingImport;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Sale;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['location', 'project', 'listingImages']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                  ->orWhere('unit_code', 'like', "%{$search}%")
                  ->orWhere('unit_type', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($projectId = $request->input('project')) {
            $query->where('project_id', $projectId);
        }

        if ($bedrooms = $request->input('bedrooms')) {
            $query->where('bedrooms', $bedrooms);
        }

        $listings = $query->latest()->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $bedroomOptions = Listing::query()
            ->select('bedrooms')
            ->whereNotNull('bedrooms')
            ->where('bedrooms', '!=', '')
            ->distinct()
            ->orderBy('bedrooms')
            ->pluck('bedrooms');

        return view('listings.units.index', compact('listings', 'projects', 'bedroomOptions'));
    }

    public function create()
    {
        $locations = Location::orderBy('project_name')->get();
        $projects = Project::orderBy('name')->get();
        $projectImages = $this->buildProjectImagesMap();

        return view('listings.units.create', compact('locations', 'projects', 'projectImages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'project_id' => 'required|exists:projects,id',
            'floor' => 'nullable|integer|min:0',
            'room_number' => 'required|string|max:255',
            'unit_code' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'price_per_room' => 'nullable|numeric|min:0',
            'price_per_sqm' => 'nullable|numeric|min:0',
            'unit_type' => 'nullable|string|max:255',
            'status' => 'required|in:available,reserved,contract,installment,transferred',
            'reservation_deposit' => 'nullable|numeric|min:0',
            'contract_payment' => 'nullable|numeric|min:0',
            'installment_15_terms' => 'nullable|numeric|min:0',
            'installment_15_terms_en' => 'nullable|numeric|min:0',
            'installment_12_terms' => 'nullable|numeric|min:0',
            'special_installment_3_terms' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'transfer_amount_en' => 'nullable|numeric|min:0',
            'transfer_fee' => 'nullable|numeric|min:0',
            'annual_common_fee' => 'nullable|numeric|min:0',
            'sinking_fund' => 'nullable|numeric|min:0',
            'utility_fee' => 'nullable|numeric|min:0',
            'total_misc_fee' => 'nullable|numeric|min:0',
            'floor_plan_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'room_layout_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'room_images' => 'nullable|array|max:10',
            'room_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $location = Location::findOrFail($validated['location_id']);
        $validated['building'] = $project->name;
        $validated['project_name'] = $location->project_name;

        if ($request->hasFile('floor_plan_image')) {
            $validated['floor_plan_image'] = $request->file('floor_plan_image')->store('listing-assets', 'public');
        } else {
            $floor = $validated['floor'] ?? null;
            if ($floor) {
                $img = ProjectImage::where('type', 'floor_plan')
                    ->where('project_id', $project->id)
                    ->where('floor', (int) $floor)
                    ->value('image_path');
                if ($img) $validated['floor_plan_image'] = $img;
            }
        }

        if ($request->hasFile('room_layout_image')) {
            $validated['room_layout_image'] = $request->file('room_layout_image')->store('listing-assets', 'public');
        } else {
            $unitType = strtoupper(trim($validated['unit_type'] ?? ''));
            if ($unitType) {
                $img = ProjectImage::where('type', 'room_layout')
                    ->where('unit_type', $unitType)
                    ->value('image_path');
                if ($img) $validated['room_layout_image'] = $img;
            }
        }

        unset($validated['room_images']);
        $validated['building'] = $project->name;
        $validated['project_name'] = $location->project_name;
        $listing = Listing::create($validated);

        if ($request->hasFile('room_images')) {
            foreach ($request->file('room_images') as $i => $file) {
                $listing->listingImages()->create([
                    'image_path' => $file->store('listing-room-images', 'public'),
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('units.index')
            ->with('success', 'Listing created successfully.');
    }

    public function show(Listing $unit)
    {
        $unit->load(['location', 'project']);

        return view('listings.units.show', compact('unit'));
    }

    public function edit(Listing $unit)
    {
        $unit->load('listingImages');
        $locations = Location::orderBy('project_name')->get();
        $projects = Project::orderBy('name')->get();
        $projectImages = $this->buildProjectImagesMap();

        return view('listings.units.edit', compact('unit', 'locations', 'projects', 'projectImages'));
    }

    private function buildProjectImagesMap(): array
    {
        // floor_plan: { project_id: { floor: path } }
        $floorPlans = ProjectImage::where('type', 'floor_plan')->get(['project_id', 'floor', 'image_path']);
        $fpMap = [];
        foreach ($floorPlans as $img) {
            $fpMap[$img->project_id][$img->floor] = $img->image_path;
        }

        // room_layout: { unit_type: path }
        $roomLayouts = ProjectImage::where('type', 'room_layout')->pluck('image_path', 'unit_type');

        return ['floor_plan' => $fpMap, 'room_layout' => $roomLayouts];
    }

    public function update(Request $request, Listing $unit)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'project_id' => 'required|exists:projects,id',
            'floor' => 'nullable|integer|min:0',
            'room_number' => 'required|string|max:255',
            'unit_code' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|string|max:255',
            'area' => 'nullable|numeric|min:0',
            'price_per_room' => 'nullable|numeric|min:0',
            'price_per_sqm' => 'nullable|numeric|min:0',
            'unit_type' => 'nullable|string|max:255',
            'status' => 'required|in:available,reserved,contract,installment,transferred',
            'reservation_deposit' => 'nullable|numeric|min:0',
            'contract_payment' => 'nullable|numeric|min:0',
            'installment_15_terms' => 'nullable|numeric|min:0',
            'installment_15_terms_en' => 'nullable|numeric|min:0',
            'installment_12_terms' => 'nullable|numeric|min:0',
            'special_installment_3_terms' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'transfer_amount_en' => 'nullable|numeric|min:0',
            'transfer_fee' => 'nullable|numeric|min:0',
            'annual_common_fee' => 'nullable|numeric|min:0',
            'sinking_fund' => 'nullable|numeric|min:0',
            'utility_fee' => 'nullable|numeric|min:0',
            'total_misc_fee' => 'nullable|numeric|min:0',
            'floor_plan_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'room_layout_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'room_images' => 'nullable|array|max:10',
            'room_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'delete_room_images' => 'nullable|array',
            'delete_room_images.*' => 'integer|exists:listing_images,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $location = Location::findOrFail($validated['location_id']);

        if ($request->hasFile('floor_plan_image')) {
            if ($unit->floor_plan_image) {
                Storage::disk('public')->delete($unit->floor_plan_image);
            }
            $validated['floor_plan_image'] = $request->file('floor_plan_image')->store('listing-assets', 'public');
        } else {
            $floor = $validated['floor'] ?? null;
            if ($floor) {
                $img = ProjectImage::where('type', 'floor_plan')
                    ->where('project_id', $project->id)
                    ->where('floor', (int) $floor)
                    ->value('image_path');
                if ($img) $validated['floor_plan_image'] = $img;
            }
        }

        if ($request->hasFile('room_layout_image')) {
            if ($unit->room_layout_image) {
                Storage::disk('public')->delete($unit->room_layout_image);
            }
            $validated['room_layout_image'] = $request->file('room_layout_image')->store('listing-assets', 'public');
        } else {
            $unitType = strtoupper(trim($validated['unit_type'] ?? ''));
            if ($unitType) {
                $img = ProjectImage::where('type', 'room_layout')
                    ->where('unit_type', $unitType)
                    ->value('image_path');
                if ($img) $validated['room_layout_image'] = $img;
            }
        }

        // Delete selected room images
        if (!empty($validated['delete_room_images'])) {
            $imagesToDelete = ListingImage::where('listing_id', $unit->id)
                ->whereIn('id', $validated['delete_room_images'])
                ->get();
            foreach ($imagesToDelete as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }
        }

        unset($validated['room_images'], $validated['delete_room_images']);
        $validated['building'] = $project->name;
        $validated['project_name'] = $location->project_name;

        $unit->update($validated);

        // Upload new room images
        if ($request->hasFile('room_images')) {
            $maxSort = $unit->listingImages()->max('sort_order') ?? -1;
            foreach ($request->file('room_images') as $file) {
                $unit->listingImages()->create([
                    'image_path' => $file->store('listing-room-images', 'public'),
                    'sort_order' => ++$maxSort,
                ]);
            }
        }

        return redirect()->route('units.index')
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $unit)
    {
        // Delete room image files
        foreach ($unit->listingImages as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Listing deleted successfully.');
    }

    public function importForm()
    {
        return view('listings.units.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ListingImport();
        Excel::import($import, $request->file('file'));

        // Auto-create Sale records for available listings that don't have one yet
        $salesCreated = 0;
        if ($import->imported > 0) {
            $availableListings = Listing::where('status', 'available')
                ->whereDoesntHave('sales', function ($q) {
                    $q->where('status', '!=', 'transferred');
                })
                ->get();

            foreach ($availableListings as $listing) {
                $sale = Sale::create([
                    'listing_id' => $listing->id,
                    'user_id'    => $request->user()?->id,
                    'status'     => 'available',
                ]);

                $sale->statusHistories()->create([
                    'status'          => 'available',
                    'previous_status' => null,
                    'notes'           => 'Auto-created from listing import',
                    'user_id'         => $request->user()?->id,
                ]);

                $salesCreated++;
            }
        }

        $message = "Imported {$import->imported} listing(s) successfully.";
        if ($salesCreated > 0) {
            $message .= " Created {$salesCreated} sale(s) on Buy/Sale pipeline.";
        }
        if ($import->skipped > 0) {
            $message .= " Skipped {$import->skipped} row(s).";
        }

        $redirect = $import->imported > 0
            ? redirect()->route('units.index')->with('success', $message)
            : back()->with('warning', $message);

        return $redirect->with('import_errors', $import->rowErrors);
    }

    public function downloadTemplate()
    {
        return Excel::download(new ListingTemplateExport(), 'listing_import_template.xlsx');
    }
}
