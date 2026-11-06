<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class RoomPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, Room $room): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($room, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }
}
