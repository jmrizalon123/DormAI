<?php

namespace App\Policies;

use App\Models\TenantPaymentSubmission;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class TenantPaymentSubmissionPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function view(User $user, TenantPaymentSubmission $submission): bool
    {
        return $user->can('manage billing')
            && $this->belongsToActiveOrganization($submission, $this->organizationContext);
    }

    public function review(User $user, TenantPaymentSubmission $submission): bool
    {
        return $this->view($user, $submission);
    }
}
