<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class PropertyPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, Property $property): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($property, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->view($user, $property);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->view($user, $property);
    }
}
