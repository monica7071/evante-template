<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuperAdminOrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount(['users', 'listings', 'sales'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('super-admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('super-admin.organizations.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'name_th'       => 'nullable|string|max:255',
            'slug'          => 'required|string|unique:organizations,slug|max:255',
            'primary_color' => 'required|string|max:7',
            'plan_id'       => 'required|exists:plans,id',
            'logo'          => 'nullable|image|max:2048',
            'is_active'     => 'boolean',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        Organization::create([
            'name'          => $validated['name'],
            'name_th'       => $validated['name_th'] ?? null,
            'slug'          => Str::slug($validated['slug']),
            'primary_color' => $validated['primary_color'],
            'plan_id'       => $plan->id,
            'storage_limit' => $plan->storage_limit,
            'logo'          => $logoPath,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function edit(Organization $organization)
    {
        $organization->loadCount(['users', 'listings', 'sales']);
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('super-admin.organizations.edit', compact('organization', 'plans'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'name_th'       => 'nullable|string|max:255',
            'primary_color' => 'required|string|max:7',
            'plan_id'       => 'required|exists:plans,id',
            'logo'          => 'nullable|image|max:2048',
            'domain'        => 'nullable|string|max:255',
            'is_active'     => 'boolean',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $organization->logo = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update([
            'name'          => $validated['name'],
            'name_th'       => $validated['name_th'] ?? null,
            'primary_color' => $validated['primary_color'],
            'plan_id'       => $plan->id,
            'storage_limit' => $plan->storage_limit,
            'domain'        => $validated['domain'] ?? null,
            'is_active'     => $request->boolean('is_active'),
        ]);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'Organization updated.');
    }

    public function toggleActive(Organization $organization)
    {
        $organization->update(['is_active' => !$organization->is_active]);

        return back()->with('success', 'Status updated.');
    }

    public function users(Organization $organization)
    {
        $users = User::where('organization_id', $organization->id)
            ->with('employee')
            ->orderBy('name')
            ->get();

        return view('super-admin.organizations.users', compact('organization', 'users'));
    }

    public function impersonate(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot impersonate Super Admin.');
        }

        session(['impersonating_as' => auth()->id()]);

        Auth::login($user);

        return redirect('/dashboard')
            ->with('info', 'Now impersonating ' . $user->name);
    }

    public function leaveImpersonate()
    {
        $originalId = session('impersonating_as');

        if (!$originalId) {
            return redirect('/dashboard');
        }

        $originalUser = User::find($originalId);

        if (!$originalUser || !$originalUser->isSuperAdmin()) {
            session()->forget('impersonating_as');
            return redirect('/dashboard');
        }

        Auth::login($originalUser);
        session()->forget('impersonating_as');

        return redirect()->route('super-admin.dashboard')
            ->with('success', 'Returned to Super Admin.');
    }
}
