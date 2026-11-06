<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Ai\ListingAssistantService;
use App\Services\Property\OccupancyService;
use App\Services\Property\PublicPropertyCatalog;
use App\Support\Platform\PlatformListingContentSettings;
use App\Support\Platform\PlatformListingMapSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, PublicPropertyCatalog $catalog, OccupancyService $occupancyService, ListingAssistantService $listingAssistant): View
    {
        $properties = $catalog->search($request);
        $mapDefaults = PlatformListingMapSettings::defaults();

        $rateBounds = [
            'floor' => 0,
            'ceil' => 15000,
            'step' => 500,
        ];

        $rateMin = $request->filled('min_rate') ? (int) $request->input('min_rate') : $rateBounds['floor'];
        $rateMax = $request->filled('max_rate') ? (int) $request->input('max_rate') : $rateBounds['ceil'];

        $marketplaceAssistantWelcome = null;
        if ($listingAssistant->isEnabled()) {
            $marketplaceAssistantWelcome = $listingAssistant->marketplaceWelcome($catalog, $occupancyService);
        }

        return view('public.home.index', [
            'properties' => $properties,
            'cities' => $catalog->cities(),
            'provinces' => $catalog->provinces(),
            'mapMarkers' => $catalog->mapMarkers($properties->getCollection()),
            'mapDefaults' => $mapDefaults,
            'totalListed' => $catalog->baseQuery()->count(),
            'rateBounds' => $rateBounds,
            'rateMin' => max($rateBounds['floor'], min($rateMin, $rateBounds['ceil'])),
            'rateMax' => max($rateBounds['floor'], min($rateMax, $rateBounds['ceil'])),
            'listingContent' => PlatformListingContentSettings::defaults(),
            'heroImageUrls' => PlatformListingContentSettings::heroImageUrls(),
            'marketplaceAssistantWelcome' => $marketplaceAssistantWelcome,
        ]);
    }
}
