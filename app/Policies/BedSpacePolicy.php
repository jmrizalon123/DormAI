<?php

namespace App\Policies;

use App\Models\BedSpace;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class BedSpacePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, BedSpace $bedSpace): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($bedSpace, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, BedSpace $bedSpace): bool
    {
        return $this->view($user, $bedSpace);
    }

    public function delete(User $user, BedSpace $bedSpace): bool
    {
        return $this->view($user, $bedSpace);
    }
}
