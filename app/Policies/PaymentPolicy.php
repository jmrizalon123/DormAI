<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class PaymentPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('manage billing')
            && $this->belongsToActiveOrganization($payment, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $user->can('manage billing');
    }
}
