<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Property\OccupancyService;
use App\Services\Property\PublicPropertyCatalog;
use App\Support\Platform\PlatformListingMapSettings;
use App\Support\Property\PropertyListingSettings;
use Illuminate\View\View;

class PropertyListingController extends Controller
{
    public function show(
        Property $property,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
    ): View {
        abort_unless($catalog->isPubliclyListed($property), 404);

        $property->load([
            'organization:id,name,slug,phone,email',
            'images' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $listing = PropertyListingSettings::forProperty($property);
        $occupancy = $occupancyService->propertySummary($property);
        $displayRate = $catalog->displayRate($property);
        $mapDefaults = PlatformListingMapSettings::defaults();

        return view('public.listings.show', compact(
            'property',
            'listing',
            'occupancy',
            'displayRate',
            'mapDefaults',
        ));
    }
}
