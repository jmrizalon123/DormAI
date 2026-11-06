<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class InvoicePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('manage billing')
            && $this->belongsToActiveOrganization($invoice, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }
}
