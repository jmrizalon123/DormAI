<?php

namespace App\Support\Platform;

use App\Models\Organization;
use Illuminate\Support\Facades\Storage;

class PlatformListingContentSettings
{
    public const SETTINGS_KEY = 'listing_content';

    public const MAX_HERO_IMAGES = 12;

    /**
     * @return array<string, mixed>
     */
    public static function configDefaults(): array
    {
        $config = config('dormai.listing_content_defaults', []);
        $heroImages = $config['hero_images'] ?? null;
        if (! is_array($heroImages) || $heroImages === []) {
            $legacy = $config['hero_image'] ?? 'images/marketplace-hero.jpg';
            $heroImages = [$legacy];
        }

        return [
            'brand_title_primary' => (string) ($config['brand_title_primary'] ?? 'Dorm'),
            'brand_title_accent' => (string) ($config['brand_title_accent'] ?? 'AI'),
            'brand_tagline' => (string) ($config['brand_tagline'] ?? ''),
            'hero_title' => (string) ($config['hero_title'] ?? ''),
            'hero_lead' => (string) ($config['hero_lead'] ?? ''),
            'search_title' => (string) ($config['search_title'] ?? 'Search listings'),
            'search_subtitle' => (string) ($config['search_subtitle'] ?? ''),
            'listings_heading' => (string) ($config['listings_heading'] ?? 'Properties for rent'),
            'listings_empty_hint' => (string) ($config['listings_empty_hint'] ?? ''),
            'listings_no_match_hint' => (string) ($config['listings_no_match_hint'] ?? ''),
            'footer_blurb' => (string) ($config['footer_blurb'] ?? ''),
            'hero_images' => array_values(array_map('strval', $heroImages)),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        $defaults = self::configDefaults();
        $organization = PlatformListingMapSettings::platformOrganization();

        if ($organization === null) {
            return self::normalizeTextDefaults($defaults);
        }

        $stored = $organization->settings[self::SETTINGS_KEY] ?? [];

        return [
            'brand_title_primary' => filled($stored['brand_title_primary'] ?? null) ? (string) $stored['brand_title_primary'] : $defaults['brand_title_primary'],
            'brand_title_accent' => filled($stored['brand_title_accent'] ?? null) ? (string) $stored['brand_title_accent'] : $defaults['brand_title_accent'],
            'brand_tagline' => array_key_exists('brand_tagline', $stored) ? (string) ($stored['brand_tagline'] ?? '') : $defaults['brand_tagline'],
            'hero_title' => filled($stored['hero_title'] ?? null) ? (string) $stored['hero_title'] : $defaults['hero_title'],
            'hero_lead' => filled($stored['hero_lead'] ?? null) ? (string) $stored['hero_lead'] : $defaults['hero_lead'],
            'search_title' => filled($stored['search_title'] ?? null) ? (string) $stored['search_title'] : $defaults['search_title'],
            'search_subtitle' => filled($stored['search_subtitle'] ?? null) ? (string) $stored['search_subtitle'] : $defaults['search_subtitle'],
            'listings_heading' => filled($stored['listings_heading'] ?? null) ? (string) $stored['listings_heading'] : $defaults['listings_heading'],
            'listings_empty_hint' => filled($stored['listings_empty_hint'] ?? null) ? (string) $stored['listings_empty_hint'] : $defaults['listings_empty_hint'],
            'listings_no_match_hint' => filled($stored['listings_no_match_hint'] ?? null) ? (string) $stored['listings_no_match_hint'] : $defaults['listings_no_match_hint'],
            'footer_blurb' => filled($stored['footer_blurb'] ?? null) ? (string) $stored['footer_blurb'] : $defaults['footer_blurb'],
        ];
    }

    /**
     * @return list<string> Stored paths or URLs
     */
    public static function heroImagePaths(): array
    {
        $defaults = self::configDefaults()['hero_images'];
        $organization = PlatformListingMapSettings::platformOrganization();

        if ($organization === null) {
            return $defaults;
        }

        $stored = $organization->settings[self::SETTINGS_KEY] ?? [];

        if (isset($stored['hero_images']) && is_array($stored['hero_images'])) {
            $paths = array_values(array_filter(array_map('strval', $stored['hero_images'])));

            return $paths !== [] ? $paths : $defaults;
        }

        if (filled($stored['hero_image'] ?? null)) {
            return [(string) $stored['hero_image']];
        }

        return $defaults;
    }

    /**
     * @return list<string>
     */
    public static function heroImageUrls(): array
    {
        return array_values(array_map(fn (string $path) => self::resolveImageUrl($path), self::heroImagePaths()));
    }

    public static function heroImageUrl(): string
    {
        $urls = self::heroImageUrls();

        return $urls[0] ?? self::resolveImageUrl(self::configDefaults()['hero_images'][0]);
    }

    /**
     * @param  array<string, string|null>  $content
     */
    public static function update(array $content): Organization
    {
        $organization = PlatformListingMapSettings::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        $settings = $organization->settings ?? [];
        $current = $settings[self::SETTINGS_KEY] ?? [];
        $textKeys = array_keys(self::normalizeTextDefaults(self::configDefaults()));

        $next = $current;
        foreach ($textKeys as $key) {
            if (array_key_exists($key, $content) && $content[$key] !== null) {
                $next[$key] = (string) $content[$key];
            }
        }

        foreach ($textKeys as $key) {
            if (! filled($next[$key] ?? null)) {
                $next[$key] = self::configDefaults()[$key];
            }
        }

        if (isset($current['hero_images'])) {
            $next['hero_images'] = $current['hero_images'];
        }

        unset($next['hero_image']);

        $settings[self::SETTINGS_KEY] = $next;
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    /**
     * @param  list<string>  $paths
     */
    public static function appendHeroImages(array $paths): Organization
    {
        $paths = array_values(array_filter($paths));
        $organization = PlatformListingMapSettings::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        if ($paths === []) {
            return $organization;
        }

        $settings = $organization->settings ?? [];
        $current = $settings[self::SETTINGS_KEY] ?? [];
        $existing = self::heroImagePaths();

        if (isset($current['hero_image']) && ! isset($current['hero_images'])) {
            $existing = [(string) $current['hero_image']];
        }

        $merged = array_values(array_unique(array_merge($existing, $paths)));
        $merged = array_slice($merged, 0, self::MAX_HERO_IMAGES);

        $current['hero_images'] = $merged;
        unset($current['hero_image']);
        $settings[self::SETTINGS_KEY] = $current;
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function removeHeroImageAt(int $index): Organization
    {
        $organization = PlatformListingMapSettings::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        $paths = self::heroImagePaths();
        if (! array_key_exists($index, $paths)) {
            abort(404);
        }

        self::deleteStoredHeroIfUploaded($paths[$index]);
        array_splice($paths, $index, 1);

        $settings = $organization->settings ?? [];
        $current = $settings[self::SETTINGS_KEY] ?? [];

        if ($paths === []) {
            $paths = self::configDefaults()['hero_images'];
        }

        $current['hero_images'] = $paths;
        unset($current['hero_image']);
        $settings[self::SETTINGS_KEY] = $current;
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function resetToConfigDefaults(): Organization
    {
        $organization = PlatformListingMapSettings::platformOrganization();
        abort_if($organization === null, 500, 'Platform organization is not configured.');

        $settings = $organization->settings ?? [];
        $stored = $settings[self::SETTINGS_KEY] ?? [];

        foreach ($stored['hero_images'] ?? [] as $path) {
            self::deleteStoredHeroIfUploaded(is_string($path) ? $path : null);
        }

        if (isset($stored['hero_image'])) {
            self::deleteStoredHeroIfUploaded(is_string($stored['hero_image']) ? $stored['hero_image'] : null);
        }

        unset($settings[self::SETTINGS_KEY]);
        $organization->update(['settings' => $settings]);

        return $organization->fresh();
    }

    public static function resolveImageUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'marketplace/') && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path).'?v='.Storage::disk('public')->lastModified($path);
        }

        if (file_exists(public_path($path))) {
            return asset($path).'?v='.filemtime(public_path($path));
        }

        return self::heroImageUrlFallback();
    }

    public static function deleteStoredHeroIfUploaded(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'marketplace/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, string>
     */
    private static function normalizeTextDefaults(array $defaults): array
    {
        return [
            'brand_title_primary' => (string) $defaults['brand_title_primary'],
            'brand_title_accent' => (string) $defaults['brand_title_accent'],
            'brand_tagline' => (string) $defaults['brand_tagline'],
            'hero_title' => (string) $defaults['hero_title'],
            'hero_lead' => (string) $defaults['hero_lead'],
            'search_title' => (string) $defaults['search_title'],
            'search_subtitle' => (string) $defaults['search_subtitle'],
            'listings_heading' => (string) $defaults['listings_heading'],
            'listings_empty_hint' => (string) $defaults['listings_empty_hint'],
            'listings_no_match_hint' => (string) $defaults['listings_no_match_hint'],
            'footer_blurb' => (string) $defaults['footer_blurb'],
        ];
    }

    private static function heroImageUrlFallback(): string
    {
        $fallback = self::configDefaults()['hero_images'][0] ?? 'images/marketplace-hero.jpg';
        if (file_exists(public_path($fallback))) {
            return asset($fallback).'?v='.filemtime(public_path($fallback));
        }

        return 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1920&q=80';
    }
}
