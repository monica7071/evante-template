<?php

namespace App\Observers;

use App\Models\Organization;
use Database\Seeders\DefaultRolesSeeder;

class OrganizationObserver
{
    public function created(Organization $organization): void
    {
        DefaultRolesSeeder::seedForOrganization($organization->id);
    }
}
