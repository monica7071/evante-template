<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['position', 'team', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($positionId = $request->input('position_id')) {
            $query->where('position_id', $positionId);
        }
        if ($teamId = $request->input('team_id')) {
            $query->where('team_id', $teamId);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $teams = Team::where('is_active', true)->orderBy('name')->get();

        return view('employee.list.index', compact('employees', 'positions', 'teams'));
    }

    public function create()
    {
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $teams = Team::where('is_active', true)->orderBy('name')->get();
        $roles = Role::where('is_active', true)->orderBy('display_name')->get();

        return view('employee.list.form', [
            'employee' => new Employee(),
            'positions' => $positions,
            'teams' => $teams,
            'roles' => $roles,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $this->validateAccount($request);

        $employee = Employee::create($validated);
        $this->handleAccount($request, $employee);

        return redirect()->route('employee.list.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $teams = Team::where('is_active', true)->orderBy('name')->get();
        $roles = Role::where('is_active', true)->orderBy('display_name')->get();

        return view('employee.list.form', [
            'employee' => $employee,
            'positions' => $positions,
            'teams' => $teams,
            'roles' => $roles,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request);
        $this->validateAccount($request, $employee);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('employees', 'public');
        }

        $employee->update($validated);
        $this->handleAccount($request, $employee);

        return redirect()->route('employee.list.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employee.list.index')->with('success', 'Employee deleted successfully.');
    }

    private function validateEmployee(Request $request): array
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'first_name_th' => 'nullable|string|max:255',
            'last_name_th' => 'nullable|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'line_id' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'position_id' => 'nullable|exists:positions,id',
            'team_id' => 'nullable|exists:teams,id',
            'hire_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'status' => 'required|in:active,probation,inactive,resigned',
            'salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('employees', 'public');
        }

        return $validated;
    }

    private function validateAccount(Request $request, ?Employee $employee = null): void
    {
        if (!$request->boolean('create_account')) {
            return;
        }

        $hasExistingUser = $employee && $employee->user_id;

        $request->validate([
            'account_email' => 'required|email',
            'account_role_id' => 'required|exists:roles,id',
            'account_password' => $hasExistingUser ? 'nullable|min:6' : 'required|min:6',
        ]);
    }

    private function handleAccount(Request $request, Employee $employee): void
    {
        if (!$request->boolean('create_account')) {
            return;
        }

        $role = Role::findOrFail($request->input('account_role_id'));

        if ($employee->user_id && $employee->user) {
            $userData = [
                'email' => $request->input('account_email'),
                'role' => $role->name,
                'role_id' => $role->id,
            ];
            if ($request->filled('account_password')) {
                $userData['password'] = Hash::make($request->input('account_password'));
            }
            $employee->user->update($userData);
        } else {
            $user = User::create([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $request->input('account_email'),
                'password' => Hash::make($request->input('account_password')),
                'role' => $role->name,
                'role_id' => $role->id,
            ]);
            $employee->update(['user_id' => $user->id]);
        }
    }
}
