<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('location')->withCount('listings');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('location', function ($q2) use ($search) {
                      $q2->where('project_name', 'like', "%{$search}%");
                  });
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        return view('listings.projects.index', compact('projects'));
    }

    public function create()
    {
        $locations = Location::orderBy('project_name')->get();

        return view('listings.projects.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'total_floors' => 'required|integer|min:1',
            'total_units' => 'nullable|integer|min:0',
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load('location')->loadCount('listings');

        // Floor plan images: keyed by floor number
        $floorPlanImages = ProjectImage::where('type', 'floor_plan')
            ->where('project_id', $project->id)
            ->pluck('image_path', 'floor');

        // Room layout images: keyed by unit_type (shared, no project filter)
        $roomLayoutImages = ProjectImage::where('type', 'room_layout')
            ->pluck('image_path', 'unit_type');

        return view('listings.projects.show', compact('project', 'floorPlanImages', 'roomLayoutImages'));
    }

    public function edit(Project $project)
    {
        $locations = Location::orderBy('project_name')->get();

        return view('listings.projects.edit', compact('project', 'locations'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'total_floors' => 'required|integer|min:1',
            'total_units' => 'nullable|integer|min:0',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    // Upload floor plan image for a specific floor
    public function uploadFloorPlan(Request $request, Project $project)
    {
        $request->validate([
            'floor' => 'required|integer|min:1',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        $floor = (int) $request->floor;

        $existing = ProjectImage::where('type', 'floor_plan')
            ->where('project_id', $project->id)
            ->where('floor', $floor)
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->image_path);
            $existing->update([
                'image_path' => $request->file('image')->store("project-assets/{$project->id}/floor", 'public'),
            ]);
        } else {
            ProjectImage::create([
                'type'       => 'floor_plan',
                'project_id' => $project->id,
                'floor'      => $floor,
                'image_path' => $request->file('image')->store("project-assets/{$project->id}/floor", 'public'),
            ]);
        }

        return back()->with('success', "Floor {$floor} plan uploaded.");
    }

    // Remove floor plan image for a specific floor
    public function removeFloorPlan(Request $request, Project $project)
    {
        $floor = (int) $request->floor;

        $record = ProjectImage::where('type', 'floor_plan')
            ->where('project_id', $project->id)
            ->where('floor', $floor)
            ->first();

        if ($record) {
            Storage::disk('public')->delete($record->image_path);
            $record->delete();
        }

        return back()->with('success', "Floor {$floor} plan removed.");
    }

    // Upload room layout image for a unit type (shared across all projects)
    public function uploadRoomLayout(Request $request, Project $project)
    {
        $request->validate([
            'unit_type' => 'required|string|max:10',
            'image'     => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $type = strtoupper(trim($request->unit_type));

        $existing = ProjectImage::where('type', 'room_layout')
            ->where('unit_type', $type)
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->image_path);
            $existing->update([
                'image_path' => $request->file('image')->store("project-assets/room-layouts", 'public'),
            ]);
        } else {
            ProjectImage::create([
                'type'       => 'room_layout',
                'unit_type'  => $type,
                'image_path' => $request->file('image')->store("project-assets/room-layouts", 'public'),
            ]);
        }

        return back()->with('success', "Room layout type {$type} uploaded.");
    }

    // Remove room layout image for a unit type
    public function removeRoomLayout(Request $request, Project $project)
    {
        $type = strtoupper(trim($request->unit_type));

        $record = ProjectImage::where('type', 'room_layout')
            ->where('unit_type', $type)
            ->first();

        if ($record) {
            Storage::disk('public')->delete($record->image_path);
            $record->delete();
        }

        return back()->with('success', "Room layout type {$type} removed.");
    }
}
