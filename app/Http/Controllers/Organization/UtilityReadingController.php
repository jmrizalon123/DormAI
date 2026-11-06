<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\UtilityMeter;
use App\Models\UtilityReading;
use App\Services\Utilities\UtilityAllocationService;
use App\Services\Utilities\UtilityBillingService;
use App\Services\Utilities\UtilityReadingService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilityReadingController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', UtilityReading::class);

        $readings = UtilityReading::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->with(['meter.property', 'meter.room', 'recorder'])
            ->when($request->filled('meter'), fn ($q) => $q->where('utility_meter_id', $request->integer('meter')))
            ->orderByDesc('reading_date')
            ->paginate(20)
            ->withQueryString();

        $meters = UtilityMeter::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->where('is_active', true)
            ->orderBy('label')
            ->get();

        return view('organization.utilities.readings.index', compact('readings', 'meters'));
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', UtilityReading::class);

        $meters = UtilityMeter::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->where('is_active', true)
            ->with(['property', 'room'])
            ->orderBy('label')
            ->get();

        return view('organization.utilities.readings.create', compact('meters'));
    }

    public function store(Request $request, OrganizationContext $organizationContext, UtilityReadingService $readingService): RedirectResponse
    {
        $this->authorize('create', UtilityReading::class);

        $validated = $request->validate([
            'utility_meter_id' => ['required', 'exists:utility_meters,id'],
            'reading_date' => ['required', 'date'],
            'current_reading' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $meter = UtilityMeter::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['utility_meter_id'])
            ->firstOrFail();

        try {
            $reading = $readingService->record(
                $meter,
                $request->user(),
                $validated['reading_date'],
                (float) $validated['current_reading'],
                $validated['notes'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('organization.utility-readings.show', $reading)->with('success', 'Reading recorded.');
    }

    public function show(UtilityReading $utilityReading): View
    {
        $this->authorize('view', $utilityReading);

        $utilityReading->load(['meter.property', 'meter.room', 'recorder', 'allocations.room', 'allocations.tenant', 'allocations.invoice']);

        return view('organization.utilities.readings.show', ['reading' => $utilityReading]);
    }

    public function destroy(UtilityReading $utilityReading, UtilityReadingService $readingService): RedirectResponse
    {
        $this->authorize('delete', $utilityReading);

        try {
            $readingService->delete($utilityReading);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('organization.utility-readings.index')->with('success', 'Reading deleted.');
    }

    public function allocate(UtilityReading $utilityReading, UtilityAllocationService $allocationService): RedirectResponse
    {
        $this->authorize('allocate', $utilityReading);

        try {
            $allocationService->calculate($utilityReading);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Utility charges allocated to rooms.');
    }

    public function bill(
        UtilityReading $utilityReading,
        OrganizationContext $organizationContext,
        UtilityBillingService $billingService,
    ): RedirectResponse {
        $this->authorize('bill', $utilityReading);

        try {
            $invoices = $billingService->createDraftInvoicesForReading(
                $utilityReading,
                $organizationContext->ensureOrganization(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $invoices->count().' draft invoice(s) created from utility allocations.');
    }
}
