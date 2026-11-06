<?php

namespace App\Http\Controllers\Organization;

use App\Enums\FloorStatus;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Floor;
use App\Services\Audit\AuditLogger;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FloorController extends Controller
{
    public function create(Building $building): View
    {
        $this->authorize('update', $building);

        return view('organization.floors.create', compact('building'));
    }

    public function store(
        Request $request,
        Building $building,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $this->authorize('create', Floor::class);
        $this->authorize('update', $building);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level_number' => ['required', 'integer', 'min:0', 'max:200'],
            'status' => ['required', 'in:'.implode(',', array_column(FloorStatus::cases(), 'value'))],
        ]);

        $floor = Floor::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
            'building_id' => $building->id,
        ]);

        $auditLogger->log('floor.created', $floor);

        return redirect()
            ->route('organization.floors.show', $floor)
            ->with('success', 'Floor added.');
    }

    public function show(Floor $floor): View
    {
        $this->authorize('view', $floor);

        $floor->load(['building.property', 'rooms.bedSpaces', 'rooms.roomType']);

        return view('organization.floors.show', compact('floor'));
    }

    public function edit(Floor $floor): View
    {
        $this->authorize('update', $floor);

        $floor->load('building.property');

        return view('organization.floors.edit', compact('floor'));
    }

    public function update(Request $request, Floor $floor, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('update', $floor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level_number' => ['required', 'integer', 'min:0', 'max:200'],
            'status' => ['required', 'in:'.implode(',', array_column(FloorStatus::cases(), 'value'))],
        ]);

        $floor->update($validated);
        $auditLogger->log('floor.updated', $floor);

        return redirect()
            ->route('organization.floors.show', $floor)
            ->with('success', 'Floor updated.');
    }

    public function destroy(Floor $floor, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $floor);

        $building = $floor->building;
        $auditLogger->log('floor.deleted', $floor);
        $floor->delete();

        return redirect()
            ->route('organization.buildings.show', $building)
            ->with('success', 'Floor removed.');
    }
}
