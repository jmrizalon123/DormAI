<?php

namespace App\Services\Reports;

use App\Enums\TenantAssignmentStatus;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use Carbon\CarbonInterface;

class TenantReportService
{
    /**
     * @return array{
     *     total_tenants: int,
     *     active_assignments: int,
     *     move_ins: int,
     *     move_outs: int,
     * }
     */
    public function build(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $organizationId = $organization->id;

        $activeAssignments = TenantAssignment::query()
            ->where('organization_id', $organizationId)
            ->where('status', TenantAssignmentStatus::Active)
            ->count();

        $moveIns = TenantAssignment::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('move_in_date', [$from->toDateString(), $to->toDateString()])
            ->count();

        $moveOuts = TenantAssignment::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('move_out_date')
            ->whereBetween('move_out_date', [$from->toDateString(), $to->toDateString()])
            ->count();

        return [
            'total_tenants' => Tenant::query()->where('organization_id', $organizationId)->count(),
            'active_assignments' => $activeAssignments,
            'move_ins' => $moveIns,
            'move_outs' => $moveOuts,
        ];
    }
}
