<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UtilityMeter;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class UtilityMeterPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage utilities');
    }

    public function view(User $user, UtilityMeter $meter): bool
    {
        return $user->can('manage utilities')
            && $this->belongsToActiveOrganization($meter, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage utilities');
    }

    public function update(User $user, UtilityMeter $meter): bool
    {
        return $this->view($user, $meter);
    }
}
