<?php

namespace App\Policies;

use App\Enums\UserAccountType;
use App\Models\User;
use App\Support\Tenancy\OrganizationContext;

class UserPolicy
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageOrganizationUsers($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->canManageOrganizationUsers($user)
            && $this->sameOrganization($model);
    }

    public function create(User $user): bool
    {
        return $this->canManageOrganizationUsers($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->canManageOrganizationUsers($user)
            && $this->sameOrganization($model)
            && ! $model->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id
            && $this->canManageOrganizationUsers($user)
            && $this->sameOrganization($model)
            && ! $model->isOrganizationOwner();
    }

    private function canManageOrganizationUsers(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->isOrganizationOwner()
            || $user->can('manage organization users');
    }

    private function sameOrganization(User $model): bool
    {
        $organizationId = $this->organizationContext->id();

        return $organizationId !== null
            && (int) $model->organization_id === $organizationId
            && $model->account_type !== UserAccountType::SuperAdmin;
    }
}
