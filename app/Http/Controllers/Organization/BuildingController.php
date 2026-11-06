<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Property;
use App\Services\Audit\AuditLogger;
use App\Services\Property\PropertyHierarchyValidator;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function create(Property $property): View
    {
        $this->authorize('update', $property);

        return view('organization.buildings.create', compact('property'));
    }

    public function store(
        Request $request,
        Property $property,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $this->authorize('create', Building::class);
        $this->authorize('update', $property);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $building = Building::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
            'property_id' => $property->id,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $auditLogger->log('building.created', $building);

        return redirect()
            ->route('organization.buildings.show', $building)
            ->with('success', 'Building added.');
    }

    public function show(Building $building, PropertyHierarchyValidator $validator): View
    {
        $this->authorize('view', $building);

        $building->load(['property', 'floors.rooms']);

        return view('organization.buildings.show', compact('building'));
    }

    public function edit(Building $building): View
    {
        $this->authorize('update', $building);

        $building->load('property');

        return view('organization.buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('update', $building);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $building->update($validated);
        $auditLogger->log('building.updated', $building);

        return redirect()
            ->route('organization.buildings.show', $building)
            ->with('success', 'Building updated.');
    }

    public function destroy(Building $building, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $building);

        $property = $building->property;
        $auditLogger->log('building.deleted', $building);
        $building->delete();

        return redirect()
            ->route('organization.properties.show', $property)
            ->with('success', 'Building removed.');
    }
}
