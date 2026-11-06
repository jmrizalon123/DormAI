<?php

namespace App\Policies;

use App\Models\ContractTemplate;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class ContractTemplatePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage contracts');
    }

    public function view(User $user, ContractTemplate $contractTemplate): bool
    {
        return $user->can('manage contracts')
            && $this->belongsToActiveOrganization($contractTemplate, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage contracts');
    }

    public function update(User $user, ContractTemplate $contractTemplate): bool
    {
        return $this->view($user, $contractTemplate);
    }

    public function delete(User $user, ContractTemplate $contractTemplate): bool
    {
        return $this->view($user, $contractTemplate);
    }
}
