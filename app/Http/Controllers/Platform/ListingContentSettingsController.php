<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\Platform\PlatformListingContentSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingContentSettingsController extends Controller
{
    public function edit(): View
    {
        $content = PlatformListingContentSettings::defaults();
        $config = PlatformListingContentSettings::configDefaults();
        $heroSlides = PlatformListingContentSettings::heroImageUrls();
        $heroPaths = PlatformListingContentSettings::heroImagePaths();

        return view('platform.settings.listing-content', compact('content', 'config', 'heroSlides', 'heroPaths'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_title_primary' => ['required', 'string', 'max:40'],
            'brand_title_accent' => ['required', 'string', 'max:40'],
            'brand_tagline' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:200'],
            'hero_lead' => ['required', 'string', 'max:500'],
            'search_title' => ['required', 'string', 'max:80'],
            'search_subtitle' => ['nullable', 'string', 'max:120'],
            'listings_heading' => ['required', 'string', 'max:80'],
            'listings_empty_hint' => ['nullable', 'string', 'max:200'],
            'listings_no_match_hint' => ['nullable', 'string', 'max:200'],
            'footer_blurb' => ['nullable', 'string', 'max:400'],
            'hero_images' => ['nullable', 'array', 'max:'.PlatformListingContentSettings::MAX_HERO_IMAGES],
            'hero_images.*' => ['image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'hero_image_url' => ['nullable', 'string', 'max:500', 'url'],
        ]);

        $payload = [
            'brand_title_primary' => $validated['brand_title_primary'],
            'brand_title_accent' => $validated['brand_title_accent'],
            'brand_tagline' => $validated['brand_tagline'] ?? '',
            'hero_title' => $validated['hero_title'],
            'hero_lead' => $validated['hero_lead'],
            'search_title' => $validated['search_title'],
            'search_subtitle' => $validated['search_subtitle'] ?? '',
            'listings_heading' => $validated['listings_heading'],
            'listings_empty_hint' => $validated['listings_empty_hint'] ?? '',
            'listings_no_match_hint' => $validated['listings_no_match_hint'] ?? '',
            'footer_blurb' => $validated['footer_blurb'] ?? '',
        ];

        PlatformListingContentSettings::update($payload);

        $newPaths = [];
        if ($request->hasFile('hero_images')) {
            foreach ($request->file('hero_images') as $file) {
                if ($file === null || ! $file->isValid()) {
                    continue;
                }
                $newPaths[] = $file->store('marketplace', 'public');
            }
        }

        if (filled($validated['hero_image_url'] ?? null)) {
            $newPaths[] = $validated['hero_image_url'];
        }

        if ($newPaths !== []) {
            PlatformListingContentSettings::appendHeroImages($newPaths);
        }

        return back()->with('success', 'Public listing page content saved.');
    }

    public function destroyHeroImage(int $index): RedirectResponse
    {
        PlatformListingContentSettings::removeHeroImageAt($index);

        return back()->with('success', 'Hero slide removed.');
    }

    public function reset(): RedirectResponse
    {
        PlatformListingContentSettings::resetToConfigDefaults();

        return back()->with('success', 'Listing page content reset to application config.');
    }
}
