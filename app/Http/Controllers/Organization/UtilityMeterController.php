<?php

namespace App\Http\Controllers\Organization;

use App\Enums\UtilityType;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\UtilityMeter;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilityMeterController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', UtilityMeter::class);

        $organizationId = $organizationContext->ensureId();

        $meters = UtilityMeter::query()
            ->where('organization_id', $organizationId)
            ->with(['property', 'room'])
            ->when($request->filled('property'), fn ($q) => $q->where('property_id', $request->integer('property')))
            ->when($request->filled('type'), fn ($q) => $q->where('utility_type', $request->string('type')))
            ->orderBy('label')
            ->paginate(20)
            ->withQueryString();

        $properties = Property::query()->where('organization_id', $organizationId)->orderBy('name')->get();

        return view('organization.utilities.meters.index', compact('meters', 'properties'));
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', UtilityMeter::class);

        return view('organization.utilities.meters.create', $this->formData($organizationContext));
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', UtilityMeter::class);

        $validated = $this->validatedMeter($request, $organizationContext);

        UtilityMeter::query()->create([
            'organization_id' => $organizationContext->ensureId(),
            ...$validated,
        ]);

        return redirect()->route('organization.utility-meters.index')->with('success', 'Meter registered.');
    }

    public function edit(UtilityMeter $utilityMeter, OrganizationContext $organizationContext): View
    {
        $this->authorize('update', $utilityMeter);

        return view('organization.utilities.meters.edit', array_merge(
            ['meter' => $utilityMeter],
            $this->formData($organizationContext),
        ));
    }

    public function update(Request $request, UtilityMeter $utilityMeter, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('update', $utilityMeter);

        $validated = $this->validatedMeter($request, $organizationContext, $utilityMeter);

        $utilityMeter->update($validated);

        return redirect()->route('organization.utility-meters.index')->with('success', 'Meter updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(OrganizationContext $organizationContext): array
    {
        $organizationId = $organizationContext->ensureId();

        return [
            'properties' => Property::query()->where('organization_id', $organizationId)->orderBy('name')->get(),
            'rooms' => Room::query()->where('organization_id', $organizationId)->with('property')->orderBy('room_number')->get(),
            'types' => UtilityType::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedMeter(Request $request, OrganizationContext $organizationContext, ?UtilityMeter $existing = null): array
    {
        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'utility_type' => ['required', 'in:electricity,water'],
            'label' => ['required', 'string', 'max:120'],
            'meter_number' => ['nullable', 'string', 'max:80'],
            'is_shared' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Property::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['property_id'])
            ->firstOrFail();

        if (! empty($validated['room_id'])) {
            Room::query()
                ->where('organization_id', $organizationContext->ensureId())
                ->where('property_id', $validated['property_id'])
                ->whereKey($validated['room_id'])
                ->firstOrFail();
        }

        $isShared = $request->boolean('is_shared', empty($validated['room_id']));

        return [
            'property_id' => $validated['property_id'],
            'room_id' => $validated['room_id'] ?? null,
            'utility_type' => UtilityType::from($validated['utility_type']),
            'label' => $validated['label'],
            'meter_number' => $validated['meter_number'] ?? null,
            'is_shared' => $isShared,
            'is_active' => $request->boolean('is_active', $existing?->is_active ?? true),
        ];
    }
}
