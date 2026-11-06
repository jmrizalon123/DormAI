<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UtilityRate;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class UtilityRatePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage utilities');
    }

    public function create(User $user): bool
    {
        return $user->can('manage utilities');
    }

    public function update(User $user, UtilityRate $utilityRate): bool
    {
        return $user->can('manage utilities')
            && $this->belongsToActiveOrganization($utilityRate, $this->organizationContext);
    }
}
