<?php

namespace App\Policies;

use App\Models\PlatformContactMessage;
use App\Models\User;

class PlatformContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Organization panel: inbox for messages routed to this org’s listings.
     */
    public function viewOrganizationInbox(User $user): bool
    {
        return $user->organization_id !== null;
    }

    public function view(User $user, PlatformContactMessage $platformContactMessage): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->organization_id === null || $platformContactMessage->organization_id === null) {
            return false;
        }

        return (int) $user->organization_id === (int) $platformContactMessage->organization_id;
    }

    public function reply(User $user, PlatformContactMessage $platformContactMessage): bool
    {
        return $this->view($user, $platformContactMessage) && $platformContactMessage->isOpen();
    }

    public function close(User $user, PlatformContactMessage $platformContactMessage): bool
    {
        return $this->reply($user, $platformContactMessage);
    }
}
