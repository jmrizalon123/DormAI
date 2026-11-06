<?php

namespace App\Support\Property;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PropertyListingSettings
{
    /**
     * @return list<string, mixed>
     */
    public static function validationRules(): array
    {
        $amenityKeys = collect(config('dormai.property_amenity_presets', []))
            ->pluck('key')
            ->all();

        return [
            'listing.tagline' => ['nullable', 'string', 'max:160'],
            'listing.highlights' => ['nullable', 'string', 'max:3000'],
            'listing.amenities' => ['nullable', 'array'],
            'listing.amenities.*' => ['string', Rule::in($amenityKeys)],
            'listing.custom_amenities' => ['nullable', 'string', 'max:2000'],
            'listing.nearby_landmarks' => ['nullable', 'string', 'max:1000'],
            'listing.house_rules' => ['nullable', 'string', 'max:2000'],
            'listing.check_in_time' => ['nullable', 'date_format:H:i'],
            'listing.check_out_time' => ['nullable', 'date_format:H:i'],
            'listing.starting_rate' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'listing.min_stay_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'listing.gender_policy' => ['nullable', 'string', Rule::in(['mixed', 'female_only', 'male_only'])],
            'listing.public_listing' => ['nullable', 'boolean'],
            'listing.wifi_included' => ['nullable', 'boolean'],
            'listing.utilities_included' => ['nullable', 'boolean'],
        ];
    }

    public static function forProperty(Property $property): array
    {
        return array_merge(self::defaults(), $property->settings['listing'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'tagline' => '',
            'highlights' => [],
            'amenities' => [],
            'custom_amenities' => [],
            'nearby_landmarks' => '',
            'house_rules' => '',
            'check_in_time' => '',
            'check_out_time' => '',
            'starting_rate' => null,
            'min_stay_months' => null,
            'gender_policy' => 'mixed',
            'public_listing' => true,
            'wifi_included' => true,
            'utilities_included' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromRequest(Request $request): array
    {
        $listing = $request->input('listing', []);

        $highlights = array_slice(self::linesToList($listing['highlights'] ?? ''), 0, 8);
        $customAmenities = array_slice(self::linesToList($listing['custom_amenities'] ?? ''), 0, 12);

        return [
            'tagline' => trim((string) ($listing['tagline'] ?? '')),
            'highlights' => $highlights,
            'amenities' => array_values(array_unique($listing['amenities'] ?? [])),
            'custom_amenities' => $customAmenities,
            'nearby_landmarks' => trim((string) ($listing['nearby_landmarks'] ?? '')),
            'house_rules' => trim((string) ($listing['house_rules'] ?? '')),
            'check_in_time' => (string) ($listing['check_in_time'] ?? ''),
            'check_out_time' => (string) ($listing['check_out_time'] ?? ''),
            'starting_rate' => filled($listing['starting_rate'] ?? null) ? (float) $listing['starting_rate'] : null,
            'min_stay_months' => filled($listing['min_stay_months'] ?? null) ? (int) $listing['min_stay_months'] : null,
            'gender_policy' => (string) ($listing['gender_policy'] ?? 'mixed'),
            'public_listing' => $request->boolean('listing.public_listing'),
            'wifi_included' => $request->boolean('listing.wifi_included'),
            'utilities_included' => $request->boolean('listing.utilities_included'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mergeSettings(?array $settings, array $listing): array
    {
        $settings ??= [];
        $settings['listing'] = $listing;

        return $settings;
    }

    /**
     * @return list<string>
     */
    private static function linesToList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public static function highlightsText(array $listing): string
    {
        return implode("\n", $listing['highlights'] ?? []);
    }

    public static function customAmenitiesText(array $listing): string
    {
        return implode("\n", $listing['custom_amenities'] ?? []);
    }

    public static function genderPolicyLabel(?string $policy): string
    {
        return match ($policy) {
            'female_only' => 'Female only',
            'male_only' => 'Male only',
            default => 'Mixed',
        };
    }

    public static function formatListingTime(?string $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
        } catch (\Throwable) {
            return $time;
        }
    }
}
