<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }

    public function approve(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin() && $organization->isPending();
    }

    public function suspend(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin() && $organization->isActive();
    }

    public function reject(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin() && $organization->isPending();
    }
}
