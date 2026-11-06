<?php

namespace App\Policies;

use App\Models\Floor;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class FloorPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, Floor $floor): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($floor, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, Floor $floor): bool
    {
        return $this->view($user, $floor);
    }

    public function delete(User $user, Floor $floor): bool
    {
        return $this->view($user, $floor);
    }
}
