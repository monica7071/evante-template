<?php

namespace App\Services\AI\Tools;

use App\Models\Project;
use App\Scopes\OrganizationScope;

class ProjectInfoTool extends AbstractTool
{
    public function name(): string
    {
        return 'project_info';
    }

    public function description(): string
    {
        return 'ดึงข้อมูลโครงการอสังหาริมทรัพย์ เช่น ชื่อโครงการ สถานที่ จำนวนชั้น จำนวนยูนิต สรุปยอดขาย '
            . 'Use this when the customer asks about project details or when you need project context before searching units.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'project_id' => [
                    'type'        => 'integer',
                    'description' => 'Specific project ID to retrieve. If omitted, returns a list of all projects.',
                ],
                'include_unit_summary' => [
                    'type'        => 'boolean',
                    'description' => 'Whether to include a count breakdown of units by status (default false)',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        $baseQuery = Project::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->with('location:id,name');

        if (isset($input['project_id'])) {
            $project = $baseQuery->find($input['project_id']);

            if (! $project) {
                return $this->notFound("ไม่พบโครงการ ID {$input['project_id']}");
            }

            $data = [
                'id'           => $project->id,
                'name'         => $project->name,
                'location'     => $project->location?->name,
                'total_floors' => $project->total_floors,
                'total_units'  => $project->total_units,
            ];

            if ($input['include_unit_summary'] ?? false) {
                $data['unit_summary'] = $this->buildUnitSummary($project->id, $organizationId);
            }

            return $this->success($data);
        }

        $projects = $baseQuery->get(['id', 'name', 'total_floors', 'total_units', 'location_id']);

        $items = $projects->map(fn ($p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'location'     => $p->location?->name,
            'total_floors' => $p->total_floors,
            'total_units'  => $p->total_units,
        ])->values()->all();

        return $this->success($items, "พบ {$projects->count()} โครงการ");
    }

    private function buildUnitSummary(int $projectId, int $organizationId): array
    {
        return \App\Models\Listing::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->where('project_id', $projectId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
    }
}
