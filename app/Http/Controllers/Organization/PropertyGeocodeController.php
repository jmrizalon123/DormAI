<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Property\PropertyGeocodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyGeocodeController extends Controller
{
    public function __invoke(Request $request, PropertyGeocodeService $geocodeService): JsonResponse
    {
        $this->authorize('viewAny', Property::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:500'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $result = filled($validated['q'] ?? null)
            ? $geocodeService->search($validated['q'])
            : $geocodeService->searchFromAddressParts($validated);

        if ($result === null) {
            return response()->json(['message' => 'No location found for that address. Try pinning on the map.'], 422);
        }

        return response()->json($result);
    }
}
