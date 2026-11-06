<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomAmenity;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomAmenityController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Property::class);

        $amenities = RoomAmenity::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->orderBy('name')
            ->paginate(15);

        return view('organization.room-amenities.index', compact('amenities'));
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:64'],
        ]);

        RoomAmenity::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
        ]);

        return back()->with('success', 'Amenity added.');
    }

    public function destroy(RoomAmenity $roomAmenity): RedirectResponse
    {
        $this->authorize('update', Property::class);

        if ((int) $roomAmenity->organization_id !== app(OrganizationContext::class)->ensureId()) {
            abort(404);
        }

        $roomAmenity->delete();

        return back()->with('success', 'Amenity removed.');
    }
}
