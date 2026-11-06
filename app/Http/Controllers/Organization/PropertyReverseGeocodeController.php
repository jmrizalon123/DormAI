<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Property\PropertyGeocodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyReverseGeocodeController extends Controller
{
    public function __invoke(Request $request, PropertyGeocodeService $geocodeService): JsonResponse
    {
        $this->authorize('viewAny', Property::class);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $geocodeService->reverse(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        if ($result === null) {
            return response()->json(['message' => 'Could not resolve an address for this pin.'], 422);
        }

        return response()->json($result);
    }
}
