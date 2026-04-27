<?php

use App\Models\Position;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Position::withoutGlobalScopes()->whereNull('role_id')->each(function (Position $position) {
            // Try to find an existing role with the same name in the same org
            $role = Role::withoutGlobalScopes()
                ->where('organization_id', $position->organization_id)
                ->where('display_name', $position->name)
                ->first();

            if (!$role) {
                $slug = Str::slug($position->name, '_');

                // Ensure unique slug within org
                $baseName = $slug;
                $counter = 1;
                while (Role::withoutGlobalScopes()
                    ->where('organization_id', $position->organization_id)
                    ->where('name', $slug)
                    ->exists()
                ) {
                    $slug = $baseName . '_' . $counter++;
                }

                $role = Role::withoutGlobalScopes()->create([
                    'organization_id' => $position->organization_id,
                    'name' => $slug,
                    'display_name' => $position->name,
                    'is_default' => false,
                    'is_active' => $position->is_active,
                ]);
            }

            $position->update(['role_id' => $role->id]);
        });
    }

    public function down(): void
    {
        // No rollback needed — the previous migration drops the column
    }
};
