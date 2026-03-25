<?php

namespace App\Traits;

use App\Scopes\OrganizationScope;
use Illuminate\Support\Facades\DB;

/**
 * Auto-assigns organization_id on creating and applies OrganizationScope.
 *
 * Resolution order:
 *  1. Already set on the model → keep it
 *  2. Authenticated user has organization_id → use it
 *  3. Model has a sale_id → inherit from that sale
 *  4. Model has a sale_purchase_agreement_id → inherit from that agreement
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function ($model) {
            if (!empty($model->organization_id)) {
                return;
            }

            // From authenticated user
            if (auth()->check() && auth()->user()->organization_id) {
                $model->organization_id = auth()->user()->organization_id;
                return;
            }

            // From related sale
            if (!empty($model->sale_id)) {
                $orgId = DB::table('sales')->where('id', $model->sale_id)->value('organization_id');
                if ($orgId) {
                    $model->organization_id = $orgId;
                    return;
                }
            }

            // From related purchase agreement
            if (!empty($model->sale_purchase_agreement_id)) {
                $orgId = DB::table('sale_purchase_agreements')->where('id', $model->sale_purchase_agreement_id)->value('organization_id');
                if ($orgId) {
                    $model->organization_id = $orgId;
                    return;
                }
            }

            // From related listing
            if (!empty($model->listing_id)) {
                $orgId = DB::table('listings')->where('id', $model->listing_id)->value('organization_id');
                if ($orgId) {
                    $model->organization_id = $orgId;
                    return;
                }
            }
        });
    }
}
