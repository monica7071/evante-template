<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins bypass all organization checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Regular users must have an organization
        if (!$user->organization_id) {
            abort(403, 'No organization assigned to your account.');
        }

        // Check if organization is active
        $organization = $user->organization;

        if (!$organization || !$organization->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Your organization has been suspended. Please contact support.',
            ]);
        }

        // Store organization in request and config for easy access
        $request->attributes->set('organization', $organization);
        config(['app.organization_id' => $organization->id]);

        return $next($request);
    }
}
