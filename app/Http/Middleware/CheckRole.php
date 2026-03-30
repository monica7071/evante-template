<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        // Check dynamic role first, then fallback to ENUM
        $currentRole = $user->role_id && $user->dynamicRole
            ? $user->dynamicRole->name
            : $user->role;

        if (!in_array($currentRole, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
