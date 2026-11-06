<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UtilityReading;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class UtilityReadingPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage utilities') || $user->can('view reports');
    }

    public function view(User $user, UtilityReading $reading): bool
    {
        return ($user->can('manage utilities') || $user->can('view reports'))
            && $this->belongsToActiveOrganization($reading, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage utilities');
    }

    public function delete(User $user, UtilityReading $reading): bool
    {
        return $user->can('manage utilities')
            && $this->belongsToActiveOrganization($reading, $this->organizationContext);
    }

    public function allocate(User $user, UtilityReading $reading): bool
    {
        return $user->can('manage utilities')
            && $this->belongsToActiveOrganization($reading, $this->organizationContext);
    }

    public function bill(User $user, UtilityReading $reading): bool
    {
        return $user->can('manage utilities') && $user->can('manage billing')
            && $this->belongsToActiveOrganization($reading, $this->organizationContext);
    }
}
