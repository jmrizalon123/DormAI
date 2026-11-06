<?php

namespace App\Support\Platform;

use App\Models\Organization;
use App\Support\Tenancy\PlatformOrganization;

class PlatformListingMapSettings
{
    public const SETTINGS_KEY = 'listing_map';

    /**
     * @return array{latitude: float, longitude: float, zoom: int, label: string}
     */
    public static function configDefaults(): array
    {
        $config = config('dormai.map_defaults', []);

        return [
            'latitude' => (float) ($config['latitude'] ?? 11.7753),
            'longitude' => (float) ($config['longitude'] ?? 124.8861),
            'zoom' => (int) ($config['zoom'] ?? 13),
            'label' => (string) ($config['label'] ?? 'Catbalogan City, Samar'),
        ];
    }

    /**
     * Default map focus for the public listings marketplace and org system fallback.
     *
     * @return array{latitude: float, longitude: float, zoom: int, label: string}
     */
    public static function defaults(): array
    {
        $defaults = self::configDefaults();
        $organization = self::platformOrganization();

        if ($organization === null) {
            return $defaults;
        }

        $stored = $organization->settings[self::SETTINGS_KEY] ?? [];

        return [
            'latitude' => isset($stored['latitude']) ? (float) $stored['latitude'] : $defaults['latitude'],
            'longitude' => isset($stored['longitude']) ? (float) $stored['longitude'] : $defaults['longitude'],
            'zoom' => isset($stored['zoom']) ? (int) $stored['zoom'] : $defaults['zoom'],
            'label' => filled($stored['label'] ?? null) ? (string) $stored['label'] : $defaults['label'],
        ];
    }

    /**
     * @param  array{latitude: float, longitude: float, zoom: int, label?: string}  $map
     */
    public static function update(array $map): Organization
    {
        $organization = self::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        $settings = $organization->settings ?? [];
        $settings[self::SETTINGS_KEY] = [
            'latitude' => $map['latitude'],
            'longitude' => $map['longitude'],
            'zoom' => $map['zoom'],
            'label' => $map['label'] ?? self::configDefaults()['label'],
        ];

        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function resetToConfigDefaults(): Organization
    {
        $organization = self::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        $settings = $organization->settings ?? [];
        unset($settings[self::SETTINGS_KEY]);
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function platformOrganization(): ?Organization
    {
        try {
            return Organization::query()->find(PlatformOrganization::id());
        } catch (\Throwable) {
            return null;
        }
    }
}
