<?php

namespace App\Policies;

use App\Models\RoomType;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class RoomTypePolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function view(User $user, RoomType $roomType): bool
    {
        return $this->canManageProperties($user)
            && $this->belongsToActiveOrganization($roomType, $this->organizationContext);
    }

    public function create(User $user): bool
    {
        return $this->canManageProperties($user);
    }

    public function update(User $user, RoomType $roomType): bool
    {
        return $this->view($user, $roomType);
    }

    public function delete(User $user, RoomType $roomType): bool
    {
        return $this->view($user, $roomType);
    }
}
