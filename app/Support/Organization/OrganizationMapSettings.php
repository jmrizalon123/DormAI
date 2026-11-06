<?php

namespace App\Support\Organization;

use App\Models\Organization;
use App\Support\Platform\PlatformListingMapSettings;

class OrganizationMapSettings
{
    /**
     * @return array{latitude: float, longitude: float, zoom: int, label: string}
     */
    public static function systemDefaults(): array
    {
        return PlatformListingMapSettings::defaults();
    }

    /**
     * @return array{latitude: float, longitude: float, zoom: int, label: string}
     */
    public static function forOrganization(?Organization $organization): array
    {
        $defaults = self::systemDefaults();

        if ($organization === null) {
            return $defaults;
        }

        $stored = $organization->settings['map'] ?? [];

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
    public static function update(Organization $organization, array $map): Organization
    {
        $settings = $organization->settings ?? [];
        $settings['map'] = [
            'latitude' => $map['latitude'],
            'longitude' => $map['longitude'],
            'zoom' => $map['zoom'],
            'label' => $map['label'] ?? self::systemDefaults()['label'],
        ];

        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function resetToSystemDefaults(Organization $organization): Organization
    {
        $settings = $organization->settings ?? [];
        unset($settings['map']);
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }
}
