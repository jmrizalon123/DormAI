<?php

namespace App\Http\Controllers\Organization;

use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\Maintenance\MaintenanceService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        $organizationId = $organizationContext->ensureId();

        $workOrders = WorkOrder::query()
            ->where('organization_id', $organizationId)
            ->with(['property', 'room', 'assignee', 'tenant'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('assigned'), fn ($q) => $q->where('assigned_to', $request->integer('assigned')))
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $staff = $this->maintenanceStaff($organizationId);

        return view('organization.maintenance.work-orders.index', compact('workOrders', 'staff'));
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', WorkOrder::class);

        return view('organization.maintenance.work-orders.create', $this->formData($organizationContext));
    }

    public function store(Request $request, OrganizationContext $organizationContext, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('create', WorkOrder::class);

        $validated = $this->validated($request, $organizationContext);

        $workOrder = $maintenanceService->createStandaloneWorkOrder(
            $organizationContext->ensureOrganization(),
            $request->user(),
            $validated,
        );

        return redirect()->route('organization.work-orders.show', $workOrder)->with('success', 'Work order created.');
    }

    public function show(WorkOrder $workOrder, OrganizationContext $organizationContext): View
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['property', 'room', 'tenant', 'assignee', 'creator', 'maintenanceRequest', 'logs.user']);

        return view('organization.maintenance.work-orders.show', [
            'workOrder' => $workOrder,
            'staff' => $this->maintenanceStaff($organizationContext->ensureId()),
        ]);
    }

    public function assign(Request $request, WorkOrder $workOrder, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('assign', $workOrder);

        $validated = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        $assignee = User::query()
            ->where('organization_id', $workOrder->organization_id)
            ->whereKey($validated['assigned_to'])
            ->firstOrFail();

        try {
            $maintenanceService->assign($workOrder, $request->user(), $assignee);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Staff assigned.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('update', $workOrder);

        $validated = $request->validate([
            'status' => ['required', 'in:open,assigned,in_progress,on_hold,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $maintenanceService->updateStatus(
                $workOrder,
                $request->user(),
                WorkOrderStatus::from($validated['status']),
                $validated['note'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Work order updated.');
    }

    public function complete(Request $request, WorkOrder $workOrder, MaintenanceService $maintenanceService): RedirectResponse
    {
        $this->authorize('complete', $workOrder);

        $validated = $request->validate([
            'labor_cost' => ['required', 'numeric', 'min:0'],
            'parts_cost' => ['required', 'numeric', 'min:0'],
            'completion_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $maintenanceService->complete(
                $workOrder,
                $request->user(),
                (float) $validated['labor_cost'],
                (float) $validated['parts_cost'],
                $validated['completion_notes'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Work order marked completed.');
    }

    /**
     * @return Collection<int, User>
     */
    private function maintenanceStaff(int $organizationId)
    {
        return User::query()
            ->where('organization_id', $organizationId)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['maintenance-staff', 'property-manager', 'organization-owner']))
            ->orderBy('name')
            ->get();
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
            'rooms' => Room::query()->where('organization_id', $organizationId)->orderBy('room_number')->get(),
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
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'scheduled_for' => ['nullable', 'date'],
        ]);

        Property::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereKey($validated['property_id'])
            ->firstOrFail();

        return $validated;
    }
}
