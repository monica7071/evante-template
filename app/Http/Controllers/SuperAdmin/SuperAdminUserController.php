<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('organization')
            ->whereNotNull('organization_id')
            ->orderBy('organization_id')
            ->orderBy('name');

        if ($request->filled('org')) {
            $query->where('organization_id', $request->org);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->paginate(30)->withQueryString();
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return view('super-admin.users.index', compact('users', 'organizations'));
    }

    public function toggleActive(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot deactivate Super Admin.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated.');
    }
}
