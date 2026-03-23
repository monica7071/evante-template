<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

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

        Position::create($validated);

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

        return back()->with('success', 'Position updated successfully.');
    }

    public function toggleActive(Position $position)
    {
        $position->update(['is_active' => !$position->is_active]);

        return back()->with('success', 'Position status updated.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete position with active employees.');
        }

        $position->delete();

        return back()->with('success', 'Position deleted successfully.');
    }
}
