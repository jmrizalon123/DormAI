<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', RoomType::class);

        $roomTypes = RoomType::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->withCount('rooms')
            ->orderBy('name')
            ->paginate(15);

        return view('organization.room-types.index', compact('roomTypes'));
    }

    public function create(): View
    {
        $this->authorize('create', RoomType::class);

        return view('organization.room-types.create');
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', RoomType::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        RoomType::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
        ]);

        return redirect()
            ->route('organization.room-types.index')
            ->with('success', 'Room type created.');
    }

    public function edit(RoomType $roomType): View
    {
        $this->authorize('update', $roomType);

        return view('organization.room-types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $roomType);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $roomType->update($validated);

        return redirect()
            ->route('organization.room-types.index')
            ->with('success', 'Room type updated.');
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        $this->authorize('delete', $roomType);

        if ($roomType->rooms()->exists()) {
            return back()->withErrors(['room_type' => 'Room type is assigned to existing rooms.']);
        }

        $roomType->delete();

        return redirect()
            ->route('organization.room-types.index')
            ->with('success', 'Room type removed.');
    }
}
