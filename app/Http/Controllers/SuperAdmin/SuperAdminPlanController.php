<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('organizations')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('super-admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'required|string|unique:plans,slug|max:255',
            'storage_limit_gb' => 'required|numeric|min:1',
            'price'            => 'required|numeric|min:0',
            'description'      => 'nullable|string|max:1000',
            'is_active'        => 'boolean',
        ]);

        Plan::create([
            'name'          => $validated['name'],
            'slug'          => Str::slug($validated['slug']),
            'storage_limit' => $validated['storage_limit_gb'] * 1024,
            'price'         => $validated['price'],
            'description'   => $validated['description'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $plan->loadCount('organizations');

        return view('super-admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'storage_limit_gb' => 'required|numeric|min:1',
            'price'            => 'required|numeric|min:0',
            'description'      => 'nullable|string|max:1000',
            'is_active'        => 'boolean',
        ]);

        $plan->update([
            'name'          => $validated['name'],
            'slug'          => Str::slug($validated['slug']),
            'storage_limit' => $validated['storage_limit_gb'] * 1024,
            'price'         => $validated['price'],
            'description'   => $validated['description'] ?? null,
            'is_active'     => $request->boolean('is_active'),
        ]);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan updated.');
    }

    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Plan status updated.');
    }
}
