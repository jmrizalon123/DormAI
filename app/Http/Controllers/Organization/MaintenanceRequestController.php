<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\Maintenance\MaintenanceService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', MaintenanceRequest::class);

        $requests = MaintenanceRequest::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->with(['tenant', 'property', 'room', 'reporter', 'workOrder'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->orderByDesc('requested_at')
            ->paginate(20)
            ->withQueryString();

        return view('organization.maintenance.requests.index', ['maintenanceRequests' => $requests]);
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', MaintenanceRequest::class);

        return view('organization.maintenance.requests.create', $this->formData($organizationContext));
    }

    public function store(Request $request, OrganizationContext $organizationContext, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('create', MaintenanceRequest::class);

        $validated = $this->validated($request, $organizationContext);

        $maintenanceRequest = $maintenanceService->createRequest(
            $organizationContext->ensureOrganization(),
            $request->user(),
            $validated,
        );

        return redirect()
            ->route('organization.maintenance-requests.show', $maintenanceRequest)
            ->with('success', 'Maintenance request logged.');
    }

    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $this->authorize('view', $maintenanceRequest);

        $maintenanceRequest->load(['tenant', 'property', 'room', 'reporter', 'workOrder.assignee', 'logs.user']);

        return view('organization.maintenance.requests.show', ['maintenanceRequest' => $maintenanceRequest]);
    }

    public function cancel(Request $request, MaintenanceRequest $maintenanceRequest, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('cancel', $maintenanceRequest);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $maintenanceService->cancelRequest($maintenanceRequest, $request->user(), $validated['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request cancelled.');
    }

    public function createWorkOrder(MaintenanceRequest $maintenanceRequest, MaintenanceService $maintenanceService, Request $request): RedirectResponse
    {
        $this->authorize('createWorkOrder', $maintenanceRequest);

        try {
            $workOrder = $maintenanceService->createWorkOrderFromRequest($maintenanceRequest, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('organization.work-orders.show', $workOrder)
            ->with('success', 'Work order created.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(OrganizationContext $organizationContext): array
    {
        $organizationId = $organizationContext->ensureId();

        return [
            'tenants' => Tenant::query()->where('organization_id', $organizationId)->orderBy('last_name')->get(),
            'properties' => Property::query()->where('organization_id', $organizationId)->orderBy('name')->get(),
            'rooms' => Room::query()->where('organization_id', $organizationId)->with('property')->orderBy('room_number')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, OrganizationContext $organizationContext): array
    {
        $validated = $request->validate([
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'property_id' => ['required', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        Property::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['property_id'])
            ->firstOrFail();

        if (! empty($validated['tenant_id'])) {
            Tenant::query()
                ->where('organization_id', $organizationContext->ensureId())
                ->whereKey($validated['tenant_id'])
                ->firstOrFail();
        }

        if (! empty($validated['room_id'])) {
            Room::query()
                ->where('organization_id', $organizationContext->ensureId())
                ->where('property_id', $validated['property_id'])
                ->whereKey($validated['room_id'])
                ->firstOrFail();
        }

        return $validated;
    }
}
