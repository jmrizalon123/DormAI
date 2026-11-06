<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class ReservationPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage reservations');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->can('manage reservations')
            && $this->belongsToActiveOrganization($reservation, $this->organizationContext);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $this->view($user, $reservation);
    }
}
