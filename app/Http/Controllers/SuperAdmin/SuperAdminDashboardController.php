<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\StatusHistory;
use App\Models\User;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount([
            'users',
            'listings',
            'sales',
        ])->orderBy('created_at', 'desc')->get();

        $stats = [
            'total_orgs'         => $organizations->count(),
            'active_orgs'        => $organizations->where('is_active', true)->count(),
            'total_users'        => User::whereNotNull('organization_id')->count(),
            'total_listings'     => Listing::count(),
            'storage_alert_orgs' => $organizations->filter(fn ($o) =>
                $o->storage_limit > 0 &&
                ($o->storage_used / $o->storage_limit) >= 0.8
            )->count(),
        ];

        $recentActivity = StatusHistory::with(['sale', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.dashboard', compact('organizations', 'stats', 'recentActivity'));
    }
}
