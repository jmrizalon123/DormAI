<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class WorkOrderPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage maintenance') || $user->can('view maintenance');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return ($user->can('manage maintenance') || $user->can('view maintenance'))
            && $this->belongsToActiveOrganization($workOrder, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage maintenance');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->can('manage maintenance')
            && $this->belongsToActiveOrganization($workOrder, $this->organizationContext);
    }

    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $this->update($user, $workOrder);
    }

    public function complete(User $user, WorkOrder $workOrder): bool
    {
        return $this->update($user, $workOrder);
    }
}
