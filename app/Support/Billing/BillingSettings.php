<?php

namespace App\Support\Billing;

use App\Models\Organization;

class BillingSettings
{
    /**
     * @return array{due_day: int, invoice_prefix: string, grace_days: int, auto_generate_monthly: bool}
     */
    public static function forOrganization(Organization $organization): array
    {
        $stored = $organization->settings['billing'] ?? [];

        return array_merge(config('dormai.billing_defaults', []), $stored);
    }

    /**
     * @param  array<string, mixed>  $billing
     */
    public static function update(Organization $organization, array $billing): Organization
    {
        $settings = $organization->settings ?? [];
        $settings['billing'] = array_merge(self::forOrganization($organization), $billing);
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }
}
