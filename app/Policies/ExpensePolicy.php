<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class ExpensePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage expenses') || $user->can('view reports');
    }

    public function view(User $user, Expense $expense): bool
    {
        return ($user->can('manage expenses') || $user->can('view reports'))
            && $this->belongsToActiveOrganization($expense, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage expenses');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('manage expenses')
            && $this->belongsToActiveOrganization($expense, $this->organizationContext)
            && $expense->isEditable();
    }

    public function submit(User $user, Expense $expense): bool
    {
        return $this->update($user, $expense);
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->can('manage expenses')
            && $this->belongsToActiveOrganization($expense, $this->organizationContext);
    }

    public function attach(User $user, Expense $expense): bool
    {
        return $user->can('manage expenses')
            && $this->belongsToActiveOrganization($expense, $this->organizationContext)
            && $expense->isEditable();
    }
}
