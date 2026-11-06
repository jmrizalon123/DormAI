<?php

namespace App\Services\Organization;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrganizationApprovalService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function approve(Organization $organization, User $admin): Organization
    {
        if (! $admin->isSuperAdmin()) {
            throw new InvalidArgumentException('Only super administrators can approve organizations.');
        }

        return DB::transaction(function () use ($organization, $admin) {
            $organization->update([
                'status' => OrganizationStatus::Active,
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'rejection_reason' => null,
            ]);

            $this->auditLogger->log(
                event: 'organization.approved',
                auditable: $organization,
                properties: ['approved_by' => $admin->id],
                organizationId: $organization->id,
            );

            return $organization->fresh();
        });
    }

    public function suspend(Organization $organization, User $admin, ?string $reason = null): Organization
    {
        if (! $admin->isSuperAdmin()) {
            throw new InvalidArgumentException('Only super administrators can suspend organizations.');
        }

        return DB::transaction(function () use ($organization, $reason) {
            $organization->update([
                'status' => OrganizationStatus::Suspended,
                'rejection_reason' => $reason,
            ]);

            $this->auditLogger->log(
                event: 'organization.suspended',
                auditable: $organization,
                properties: ['reason' => $reason],
                organizationId: $organization->id,
            );

            return $organization->fresh();
        });
    }

    public function reject(Organization $organization, User $admin, string $reason): Organization
    {
        if (! $admin->isSuperAdmin()) {
            throw new InvalidArgumentException('Only super administrators can reject organizations.');
        }

        return DB::transaction(function () use ($organization, $reason) {
            $organization->update([
                'status' => OrganizationStatus::Rejected,
                'rejection_reason' => $reason,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $this->auditLogger->log(
                event: 'organization.rejected',
                auditable: $organization,
                properties: ['reason' => $reason],
                organizationId: $organization->id,
            );

            return $organization->fresh();
        });
    }
}
