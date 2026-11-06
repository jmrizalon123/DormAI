<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class ContractPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage contracts');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->can('manage contracts')
            && $this->belongsToActiveOrganization($contract, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage contracts');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $this->view($user, $contract);
    }
}
