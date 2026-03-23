<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['leader', 'parentTeam'])->withCount('employees')->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $allTeams = Team::where('is_active', true)->orderBy('name')->get();

        return view('employee.teams', compact('teams', 'employees', 'allTeams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_th' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:employees,id',
            'parent_team_id' => 'nullable|exists:teams,id',
        ]);

        Team::create($validated);

        return back()->with('success', 'Team created successfully.');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_th' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:employees,id',
            'parent_team_id' => 'nullable|exists:teams,id',
        ]);

        if ($validated['parent_team_id'] == $team->id) {
            return back()->with('error', 'Team cannot be its own parent.');
        }

        $team->update($validated);

        return back()->with('success', 'Team updated successfully.');
    }

    public function toggleActive(Team $team)
    {
        $team->update(['is_active' => !$team->is_active]);

        return back()->with('success', 'Team status updated.');
    }

    public function members(Team $team)
    {
        $members = $team->employees()->with('position')->get();
        return response()->json($members);
    }

    public function destroy(Team $team)
    {
        if ($team->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete team with active members.');
        }

        $team->delete();

        return back()->with('success', 'Team deleted successfully.');
    }
}
