<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Policies\Concerns\InteractsWithOrganization;
use App\Support\Tenancy\OrganizationContext;

class AnnouncementPolicy
{
    use InteractsWithOrganization;

    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function viewAny(User $user): bool
    {
        return $user->can('manage organization settings');
    }

    public function create(User $user): bool
    {
        return $user->can('manage organization settings');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('manage organization settings')
            && $this->belongsToActiveOrganization($announcement, $this->organizationContext);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
