<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\Platform\PlatformListingMapSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingMapSettingsController extends Controller
{
    public function edit(): View
    {
        $map = PlatformListingMapSettings::defaults();
        $config = PlatformListingMapSettings::configDefaults();

        return view('platform.settings.listing-map', compact('map', 'config'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'zoom' => ['required', 'integer', 'min:3', 'max:18'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        PlatformListingMapSettings::update([
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'zoom' => (int) $validated['zoom'],
            'label' => $validated['label'] ?? PlatformListingMapSettings::configDefaults()['label'],
        ]);

        return back()->with('success', 'Public listing map defaults saved.');
    }

    public function reset(): RedirectResponse
    {
        PlatformListingMapSettings::resetToConfigDefaults();

        return back()->with('success', 'Listing map defaults reset to application config.');
    }
}
