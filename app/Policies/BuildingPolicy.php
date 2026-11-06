<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class BuildingPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, Building $building): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($building, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, Building $building): bool
    {
        return $this->view($user, $building);
    }

    public function delete(User $user, Building $building): bool
    {
        return $this->view($user, $building);
    }
}
