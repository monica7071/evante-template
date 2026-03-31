<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RoundRobinAssignmentService
{
    /**
     * Assign a sale to the next agent in round-robin order.
     * Returns the assigned User, or null if no agents available.
     */
    public static function assignToSale(Sale $sale): ?User
    {
        $orgId = $sale->organization_id;
        if (! $orgId) {
            return null;
        }

        $agent = static::nextAgent($orgId);
        if (! $agent) {
            return null;
        }

        $sale->user_id = $agent->id;
        $sale->save();

        return $agent;
    }

    /**
     * Get the next agent in round-robin for the given organization.
     */
    public static function nextAgent(int $organizationId): ?User
    {
        $agents = User::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('role', 'agent')
                  ->orWhereHas('dynamicRole', fn ($r) => $r->where('name', 'agent'));
            })
            ->orderBy('id')
            ->get();

        if ($agents->isEmpty()) {
            return null;
        }

        $cacheKey = "org:{$organizationId}:round_robin_index";
        $lastIndex = Cache::get($cacheKey, -1);
        $nextIndex = ($lastIndex + 1) % $agents->count();

        Cache::put($cacheKey, $nextIndex, now()->addYear());

        return $agents[$nextIndex];
    }
}
