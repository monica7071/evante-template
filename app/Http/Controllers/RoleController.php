<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $roles = Role::withCount('users')->get();
        $permissions = Permission::orderBy('module')->orderBy('action')->get();

        // Build menu-structured permission map matching actual navigation
        $menuPermissions = self::buildMenuStructure($permissions);

        return view('employee.roles.index', compact('roles', 'permissions', 'menuPermissions'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|regex:/^[a-z_]+$/|unique:roles,name,NULL,id,organization_id,' . $user->organization_id,
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_default' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('employee.roles.index')->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('employee.roles.index')->with('success', 'Role updated successfully.');
    }

    private static function buildMenuStructure($permissions): array
    {
        // Index permissions by name for easy lookup
        $byName = $permissions->keyBy('name');

        // Helper to pluck permission objects by names
        $pick = function (array $names) use ($byName) {
            $result = [];
            foreach ($names as $name) {
                if ($byName->has($name)) {
                    $result[] = $byName->get($name);
                }
            }
            return $result;
        };

        // Action label mapping
        $actionLabels = [
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'import' => 'Import',
            'advance' => 'Advance Stage',
            'cancel' => 'Cancel',
            'deal_slip' => 'Deal Slip',
            'remarks' => 'Remarks',
            'export' => 'Export PDF',
            'manage_budget' => 'Manage Budget',
            'transfer' => 'Transfer',
            'upload' => 'Upload',
            'manage_mappings' => 'Manage Mappings',
            'manage' => 'Manage',
            'download' => 'Download',
        ];

        return [
            [
                'label' => 'Overview',
                'icon' => 'bi-speedometer2',
                'children' => [],
                'permissions' => $pick(['dashboard.view']),
            ],
            [
                'label' => 'Buy/Sale',
                'icon' => 'bi-shuffle',
                'children' => [],
                'permissions' => $pick([
                    'buy_sale.view', 'buy_sale.create', 'buy_sale.edit',
                    'buy_sale.advance', 'buy_sale.cancel', 'buy_sale.deal_slip', 'buy_sale.remarks',
                ]),
            ],
            [
                'label' => 'Listing Setting',
                'icon' => 'bi-buildings',
                'children' => [
                    [
                        'label' => 'Location',
                        'permissions' => $pick([
                            'listing_locations.view', 'listing_locations.create',
                            'listing_locations.edit', 'listing_locations.delete',
                        ]),
                    ],
                    [
                        'label' => 'Project',
                        'permissions' => $pick([
                            'listing_projects.view', 'listing_projects.create',
                            'listing_projects.edit', 'listing_projects.delete',
                        ]),
                    ],
                    [
                        'label' => 'Listing',
                        'permissions' => $pick([
                            'listing_units.view', 'listing_units.create',
                            'listing_units.edit', 'listing_units.delete', 'listing_units.import',
                        ]),
                    ],
                ],
                'permissions' => [],
            ],
            [
                'label' => 'Floor Plan',
                'icon' => 'bi-grid-3x3-gap',
                'children' => [],
                'permissions' => $pick(['floor_plan.view']),
            ],
            [
                'label' => 'Report',
                'icon' => 'bi-graph-up-arrow',
                'children' => [],
                'permissions' => $pick(['report.view', 'report.export', 'report.manage_budget']),
            ],
            [
                'label' => 'Finance',
                'icon' => 'bi-cash-coin',
                'children' => [],
                'permissions' => $pick(['finance.view', 'finance.transfer']),
            ],
            [
                'label' => 'Activity',
                'icon' => 'bi-lightning-charge',
                'children' => [],
                'permissions' => $pick(['activity.view']),
            ],
            [
                'label' => 'Template',
                'icon' => 'bi-file-earmark-text',
                'children' => [],
                'permissions' => $pick([
                    'templates.view', 'templates.upload', 'templates.manage_mappings', 'templates.delete',
                ]),
            ],
            [
                'label' => 'Employee',
                'icon' => 'bi-people-fill',
                'children' => [
                    [
                        'label' => 'Company Information',
                        'permissions' => $pick(['employee_company.view', 'employee_company.edit']),
                    ],
                    [
                        'label' => 'Profile Information',
                        'permissions' => $pick(['employee_profile_fields.view', 'employee_profile_fields.manage']),
                    ],
                    [
                        'label' => 'Employee List',
                        'permissions' => $pick([
                            'employee_list.view', 'employee_list.create',
                            'employee_list.edit', 'employee_list.delete',
                        ]),
                    ],
                    [
                        'label' => 'Position',
                        'permissions' => $pick(['employee_positions.view', 'employee_positions.manage']),
                    ],
                    [
                        'label' => 'Team',
                        'permissions' => $pick(['employee_teams.view', 'employee_teams.manage']),
                    ],
                ],
                'permissions' => [],
            ],
            [
                'label' => 'Contracts',
                'icon' => 'bi-file-earmark-check',
                'children' => [],
                'permissions' => $pick(['contracts.view', 'contracts.create', 'contracts.download']),
            ],
            [
                'label' => 'Admin Chat',
                'icon' => 'bi-chat-dots',
                'children' => [],
                'permissions' => $pick(['admin_chat.view', 'admin_chat.manage']),
            ],
            [
                'label' => 'Settings',
                'icon' => 'bi-gear',
                'children' => [],
                'permissions' => $pick(['roles.view', 'roles.manage']),
            ],
        ];
    }

    public function destroy(Role $role)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403);
        }

        if ($role->is_default) {
            return back()->with('error', 'Cannot delete a default role.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a role that has assigned users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('employee.roles.index')->with('success', 'Role deleted successfully.');
    }
}
