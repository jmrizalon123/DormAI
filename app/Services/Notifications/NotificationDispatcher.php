<?php

namespace App\Services\Notifications;

use App\Enums\UserStatus;
use App\Models\NotificationDispatchLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificationDispatcher
{
    public function notifyUser(
        User $user,
        Notification $notification,
        ?string $dedupeKey = null,
        ?string $kind = null,
        ?Model $subject = null,
    ): bool {
        if (! $user->isActive()) {
            return false;
        }

        if ($dedupeKey && NotificationDispatchLog::query()->where('dedupe_key', $dedupeKey)->exists()) {
            return false;
        }

        $user->notify($notification);

        if ($dedupeKey) {
            NotificationDispatchLog::query()->create([
                'dedupe_key' => $dedupeKey,
                'user_id' => $user->id,
                'kind' => $kind ?? class_basename($notification),
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id' => $subject?->getKey(),
                'sent_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * @param  list<string>  $permissions  User needs any one of these permissions.
     */
    public function notifyOrganizationStaff(
        Organization $organization,
        Notification $notification,
        array $permissions,
        ?string $dedupeKeyPrefix = null,
        ?Model $subject = null,
    ): void {
        $this->organizationStaff($organization, $permissions)->each(function (User $user) use (
            $notification,
            $dedupeKeyPrefix,
            $subject
        ) {
            $dedupe = $dedupeKeyPrefix ? "{$dedupeKeyPrefix}:user:{$user->id}" : null;
            $this->notifyUser($user, $notification, $dedupe, 'staff_broadcast', $subject);
        });
    }

    /**
     * @param  list<string>  $permissions
     * @return Collection<int, User>
     */
    public function organizationStaff(Organization $organization, array $permissions): Collection
    {
        return User::query()
            ->where('organization_id', $organization->id)
            ->where('status', UserStatus::Active)
            ->get()
            ->filter(function (User $user) use ($permissions) {
                foreach ($permissions as $permission) {
                    if ($user->can($permission)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }
}
