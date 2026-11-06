<?php

namespace App\Services\Property;

use Illuminate\Support\Facades\Http;

class PropertyGeocodeService
{
    /**
     * @return array{latitude: float, longitude: float, label: string}|null
     */
    public function search(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'DormAI').' Property Geocoder',
            'Accept-Language' => 'en',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'ph',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $result = $response->json()[0] ?? null;
        if (! is_array($result)) {
            return null;
        }

        return [
            'latitude' => (float) $result['lat'],
            'longitude' => (float) $result['lon'],
            'label' => (string) ($result['display_name'] ?? $query),
        ];
    }

    /**
     * @param  array<string, string|null>  $parts
     */
    public function searchFromAddressParts(array $parts): ?array
    {
        $line = collect([
            $parts['address_line1'] ?? null,
            $parts['address_line2'] ?? null,
            $parts['city'] ?? null,
            $parts['province'] ?? null,
            $parts['postal_code'] ?? null,
            'Philippines',
        ])->filter()->implode(', ');

        return $this->search($line);
    }

    /**
     * @return array{
     *     latitude: float,
     *     longitude: float,
     *     label: string,
     *     address_line1: string,
     *     address_line2: string|null,
     *     city: string,
     *     province: string,
     *     postal_code: string|null
     * }|null
     */
    public function reverse(float $latitude, float $longitude): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'DormAI').' Property Geocoder',
            'Accept-Language' => 'en',
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        /** @var array<string, mixed>|null $result */
        $result = $response->json();
        if (! is_array($result)) {
            return null;
        }

        /** @var array<string, string> $address */
        $address = $result['address'] ?? [];

        $road = trim($address['road'] ?? $address['pedestrian'] ?? $address['footway'] ?? '');
        $houseNumber = trim($address['house_number'] ?? '');
        $line1 = trim($houseNumber !== '' ? $houseNumber.' '.$road : $road);

        $line2 = $address['suburb']
            ?? $address['neighbourhood']
            ?? $address['quarter']
            ?? $address['hamlet']
            ?? null;

        $city = $address['city']
            ?? $address['town']
            ?? $address['municipality']
            ?? $address['village']
            ?? $address['county']
            ?? '';

        $province = $address['state']
            ?? $address['province']
            ?? $address['region']
            ?? '';

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'label' => (string) ($result['display_name'] ?? ''),
            'address_line1' => $line1,
            'address_line2' => $line2 ? (string) $line2 : null,
            'city' => (string) $city,
            'province' => (string) $province,
            'postal_code' => isset($address['postcode']) ? (string) $address['postcode'] : null,
        ];
    }
}
