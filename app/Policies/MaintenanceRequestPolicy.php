<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class MaintenanceRequestPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage maintenance') || $user->can('view maintenance');
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return ($user->can('manage maintenance') || $user->can('view maintenance'))
            && $this->belongsToActiveOrganization($maintenanceRequest, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage maintenance');
    }

    public function cancel(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $user->can('manage maintenance')
            && $this->belongsToActiveOrganization($maintenanceRequest, $this->organizationContext)
            && $maintenanceRequest->canBeCancelled();
    }

    public function createWorkOrder(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $user->can('manage maintenance')
            && $this->belongsToActiveOrganization($maintenanceRequest, $this->organizationContext)
            && $maintenanceRequest->canCreateWorkOrder();
    }
}
