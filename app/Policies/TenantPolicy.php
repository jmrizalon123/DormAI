<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class TenantPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage tenants');
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->can('manage tenants')
            && $this->belongsToActiveOrganization($tenant, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage tenants');
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->view($user, $tenant);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->view($user, $tenant);
    }
}
