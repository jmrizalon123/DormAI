<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\Property\PropertyImageService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyImageController extends Controller
{
    public function store(
        Request $request,
        Property $property,
        OrganizationContext $organizationContext,
        PropertyImageService $propertyImageService,
    ): RedirectResponse {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $propertyImageService->storeUploads(
            $property,
            $organizationContext->ensureId(),
            [$validated['image']],
        );

        return back()->with('success', 'Image uploaded.');
    }

    public function destroy(Property $property, PropertyImage $propertyImage): RedirectResponse
    {
        $this->authorize('update', $property);

        if ((int) $propertyImage->property_id !== (int) $property->id) {
            abort(404);
        }

        Storage::disk('public')->delete($propertyImage->path);
        $propertyImage->delete();

        return back()->with('success', 'Image removed.');
    }
}
