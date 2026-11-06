<?php

namespace App\Services\Property;

use App\Enums\OrganizationStatus;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Support\Property\PropertyListingSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicPropertyCatalog
{
    public function baseQuery(): Builder
    {
        return Property::query()
            ->where('status', PropertyStatus::Active)
            ->whereHas('organization', fn (Builder $query) => $query->where('status', OrganizationStatus::Active))
            ->where(function (Builder $query) {
                $query->where('settings->listing->public_listing', true)
                    ->orWhereNull('settings->listing->public_listing');
            });
    }

    public function isPubliclyListed(Property $property): bool
    {
        if ($property->status !== PropertyStatus::Active) {
            return false;
        }

        if ($property->organization?->status !== OrganizationStatus::Active) {
            return false;
        }

        return PropertyListingSettings::forProperty($property)['public_listing'] ?? true;
    }

    public function search(Request $request): LengthAwarePaginator
    {
        $query = $this->baseQuery()
            ->with([
                'organization:id,name,slug',
                'images' => fn ($q) => $q->orderBy('sort_order')->limit(3),
            ])
            ->withMin('rooms as min_room_rate', 'monthly_rate');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('province', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($city = trim((string) $request->input('city'))) {
            $query->where('city', $city);
        }

        if ($province = trim((string) $request->input('province'))) {
            $query->where('province', $province);
        }

        if ($request->filled('min_rate')) {
            $min = (float) $request->input('min_rate');
            $query->where(function (Builder $builder) use ($min) {
                $builder->where('settings->listing->starting_rate', '>=', $min)
                    ->orWhereHas('rooms', fn (Builder $room) => $room->where('monthly_rate', '>=', $min));
            });
        }

        if ($request->filled('max_rate')) {
            $max = (float) $request->input('max_rate');
            $query->where(function (Builder $builder) use ($max) {
                $builder->where('settings->listing->starting_rate', '<=', $max)
                    ->orWhereHas('rooms', fn (Builder $room) => $room->where('monthly_rate', '<=', $max));
            });
        }

        return $query
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * @return Collection<int, string>
     */
    public function cities(): Collection
    {
        return $this->baseQuery()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    /**
     * @return Collection<int, string>
     */
    public function provinces(): Collection
    {
        return $this->baseQuery()
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');
    }

    public function displayRate(Property $property): ?float
    {
        $listing = PropertyListingSettings::forProperty($property);
        if (filled($listing['starting_rate'])) {
            return (float) $listing['starting_rate'];
        }

        $min = $property->min_room_rate ?? $property->getAttribute('min_room_rate');

        return $min !== null ? (float) $min : null;
    }

    /**
     * @param  Collection<int, Property>|LengthAwarePaginator  $properties
     * @return list<array<string, mixed>>
     */
    public function mapMarkers(Collection|LengthAwarePaginator $properties): array
    {
        return collect($properties)->map(function (Property $property) {
            if (! $property->hasCoordinates()) {
                return null;
            }

            $rate = $this->displayRate($property);

            return [
                'id' => $property->id,
                'name' => $property->name,
                'lat' => (float) $property->latitude,
                'lng' => (float) $property->longitude,
                'location' => collect([$property->city, $property->province])->filter()->join(', '),
                'rate' => $rate !== null ? '₱'.number_format($rate, 0).'/mo' : null,
                'url' => route('public.listings.show', $property),
            ];
        })->filter()->values()->all();
    }
}
