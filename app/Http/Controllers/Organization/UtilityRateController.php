<?php

namespace App\Http\Controllers\Organization;

use App\Enums\UtilityType;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\UtilityRate;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilityRateController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', UtilityRate::class);

        $organizationId = $organizationContext->ensureId();
        $properties = Property::query()->where('organization_id', $organizationId)->orderBy('name')->get();
        $propertyId = $request->integer('property') ?: $properties->first()?->id;

        $rates = UtilityRate::query()
            ->where('organization_id', $organizationId)
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->with('property')
            ->orderByDesc('effective_from')
            ->paginate(15)
            ->withQueryString();

        return view('organization.utilities.rates.index', compact('rates', 'properties', 'propertyId'));
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', UtilityRate::class);

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'utility_type' => ['required', 'in:electricity,water'],
            'rate_per_unit' => ['required', 'numeric', 'min:0.0001'],
            'unit_label' => ['nullable', 'string', 'max:32'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        Property::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['property_id'])
            ->firstOrFail();

        $type = UtilityType::from($validated['utility_type']);

        UtilityRate::query()->create([
            'organization_id' => $organizationContext->ensureId(),
            'property_id' => $validated['property_id'],
            'utility_type' => $type,
            'rate_per_unit' => $validated['rate_per_unit'],
            'unit_label' => $validated['unit_label'] ?: $type->defaultUnit(),
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Utility rate saved.');
    }

    public function update(Request $request, UtilityRate $utilityRate): RedirectResponse
    {
        $this->authorize('update', $utilityRate);

        $validated = $request->validate([
            'rate_per_unit' => ['required', 'numeric', 'min:0.0001'],
            'effective_to' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $utilityRate->update([
            'rate_per_unit' => $validated['rate_per_unit'],
            'effective_to' => $validated['effective_to'] ?? $utilityRate->effective_to,
            'is_active' => $request->boolean('is_active', $utilityRate->is_active),
        ]);

        return back()->with('success', 'Utility rate updated.');
    }
}
