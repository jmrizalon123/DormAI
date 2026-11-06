<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StorePropertyRequest;
use App\Http\Requests\Organization\UpdatePropertyRequest;
use App\Models\Property;
use App\Services\Audit\AuditLogger;
use App\Services\Property\OccupancyService;
use App\Services\Property\PropertyImageService;
use App\Support\Organization\OrganizationMapSettings;
use App\Support\Property\PropertyListingSettings;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Property::class);

        $organizationId = $organizationContext->ensureId();

        $properties = Property::query()
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate(15);

        $mapProperties = Property::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'latitude', 'longitude', 'city', 'province', 'status']);

        $mapDefaults = OrganizationMapSettings::forOrganization(auth()->user()?->organization);

        $totalPropertyCount = Property::query()
            ->where('organization_id', $organizationId)
            ->count();

        $mapMarkers = $mapProperties->map(fn (Property $property) => [
            'id' => $property->id,
            'name' => $property->name,
            'code' => $property->code,
            'lat' => (float) $property->latitude,
            'lng' => (float) $property->longitude,
            'location' => collect([$property->city, $property->province])->filter()->join(', '),
            'status' => $property->status->label(),
            'url' => route('organization.properties.show', $property),
        ])->values();

        return view('organization.properties.index', compact(
            'properties',
            'mapMarkers',
            'mapDefaults',
            'mapProperties',
            'totalPropertyCount',
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('organization.properties.create');
    }

    public function store(
        StorePropertyRequest $request,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
        PropertyImageService $propertyImageService,
    ): RedirectResponse {
        $validated = $request->validated();
        $listing = PropertyListingSettings::fromRequest($request);

        $property = Property::query()->create([
            ...collect($validated)->except(['images', 'listing'])->all(),
            'organization_id' => $organizationContext->ensureId(),
            'country' => 'PH',
            'settings' => PropertyListingSettings::mergeSettings(null, $listing),
        ]);

        $uploaded = $propertyImageService->storeUploads(
            $property,
            $organizationContext->ensureId(),
            $request->file('images', []) ?? [],
        );

        $auditLogger->log('property.created', $property);

        $message = 'Property created successfully.';
        if ($uploaded > 0) {
            $message .= ' '.$uploaded.' image'.($uploaded === 1 ? '' : 's').' uploaded.';
        }

        return redirect()
            ->route('organization.properties.show', $property)
            ->with('success', $message);
    }

    public function show(Property $property, OccupancyService $occupancyService): View
    {
        $this->authorize('view', $property);

        $property->load(['buildings.floors', 'images']);
        $summary = $occupancyService->propertySummary($property);

        return view('organization.properties.show', compact('property', 'summary'));
    }

    public function edit(Property $property): View
    {
        $this->authorize('update', $property);

        $property->load(['images' => fn ($query) => $query->orderBy('sort_order')]);

        return view('organization.properties.edit', compact('property'));
    }

    public function update(
        UpdatePropertyRequest $request,
        Property $property,
        AuditLogger $auditLogger,
        OrganizationContext $organizationContext,
        PropertyImageService $propertyImageService,
    ): RedirectResponse {
        $validated = $request->validated();
        $listing = PropertyListingSettings::fromRequest($request);

        $property->update([
            ...collect($validated)->except(['listing', 'images'])->all(),
            'settings' => PropertyListingSettings::mergeSettings($property->settings, $listing),
        ]);

        $uploaded = $propertyImageService->storeUploads(
            $property,
            $organizationContext->ensureId(),
            $request->file('images', []) ?? [],
        );

        $auditLogger->log('property.updated', $property);

        $message = 'Property updated successfully.';
        if ($uploaded > 0) {
            $message .= ' '.$uploaded.' image'.($uploaded === 1 ? '' : 's').' uploaded.';
        }

        return redirect()
            ->route('organization.properties.show', $property)
            ->with('success', $message);
    }

    public function destroy(Property $property, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $property);

        $auditLogger->log('property.deleted', $property);
        $property->delete();

        return redirect()
            ->route('organization.properties.index')
            ->with('success', 'Property removed.');
    }
}
