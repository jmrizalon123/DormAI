<?php

namespace App\Support\Notifications;

use App\Models\Organization;
use App\Models\User;

class NotificationSettings
{
    /**
     * @return array{
     *     email_enabled: bool,
     *     in_app_enabled: bool,
     *     payment_reminder_days: array<int, int>,
     *     contract_expiry_days: array<int, int>
     * }
     */
    public static function forOrganization(?Organization $organization): array
    {
        $stored = $organization?->settings['notifications'] ?? [];

        return array_merge(config('dormai.notification_defaults', []), $stored);
    }

    /**
     * @return list<string>
     */
    public static function channelsFor(User $user, ?Organization $organization = null): array
    {
        $settings = self::forOrganization($organization ?? $user->organization);

        $channels = [];

        if ($settings['in_app_enabled'] ?? true) {
            $channels[] = 'database';
        }

        if (($settings['email_enabled'] ?? true) && $user->email) {
            $channels[] = 'mail';
        }

        return $channels ?: ['database'];
    }
}
