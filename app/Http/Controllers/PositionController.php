<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('employees')->orderBy('level')->get();
        return view('employee.positions', compact('positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_th' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $position = Position::create($validated);

        // Auto-create linked Role
        $slug = Str::slug($position->name, '_');
        $baseName = $slug;
        $counter = 1;
        $orgId = auth()->user()->organization_id;

        while (Role::where('name', $slug)->where('organization_id', $orgId)->exists()) {
            $slug = $baseName . '_' . $counter++;
        }

        $role = Role::create([
            'name' => $slug,
            'display_name' => $position->name,
            'is_default' => false,
            'is_active' => true,
        ]);

        $position->update(['role_id' => $role->id]);

        return back()->with('success', 'Position created successfully.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_th' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $position->update($validated);

        // Sync linked Role name
        if ($position->role) {
            $slug = Str::slug($position->name, '_');
            $baseName = $slug;
            $counter = 1;

            while (Role::where('name', $slug)
                ->where('id', '!=', $position->role_id)
                ->where('organization_id', $position->role->organization_id)
                ->exists()
            ) {
                $slug = $baseName . '_' . $counter++;
            }

            $position->role->update([
                'name' => $slug,
                'display_name' => $position->name,
            ]);
        }

        return back()->with('success', 'Position updated successfully.');
    }

    public function toggleActive(Position $position)
    {
        $position->update(['is_active' => !$position->is_active]);

        // Sync active status to linked Role
        if ($position->role) {
            $position->role->update(['is_active' => $position->is_active]);
        }

        return back()->with('success', 'Position status updated.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete position with active employees.');
        }

        // Delete linked Role (detach permissions first)
        if ($position->role) {
            $position->role->permissions()->detach();
            $position->role->delete();
        }

        $position->delete();

        return back()->with('success', 'Position deleted successfully.');
    }
}
