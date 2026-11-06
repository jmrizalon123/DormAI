<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class ExpenseCategoryPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage expenses');
    }

    public function create(User $user): bool
    {
        return $user->can('manage expenses');
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $user->can('manage expenses')
            && $this->belongsToActiveOrganization($category, $this->organizationContext);
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
