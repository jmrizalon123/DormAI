<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Services\Maintenance\MaintenanceService;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $maintenanceRequests = MaintenanceRequest::query()
            ->where('tenant_id', $tenant->id)
            ->with(['room', 'property', 'workOrder'])
            ->orderByDesc('requested_at')
            ->paginate(15);

        return view('portal.maintenance.index', compact('maintenanceRequests', 'tenant'));
    }

    public function create(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant()->load('activeAssignment.room');

        return view('portal.maintenance.create', compact('tenant'));
    }

    public function store(
        Request $request,
        TenantPortalContext $tenantPortalContext,
        MaintenanceService $maintenanceService,
    ): RedirectResponse {
        $tenant = $tenantPortalContext->ensureTenant();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        try {
            $maintenanceRequest = $maintenanceService->createRequestForTenant(
                $tenant,
                $request->user(),
                $validated,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('portal.maintenance.show', $maintenanceRequest)
            ->with('success', 'Maintenance request submitted.');
    }

    public function show(MaintenanceRequest $portalMaintenanceRequest): View
    {
        $portalMaintenanceRequest->load(['property', 'room', 'workOrder', 'logs.user']);

        return view('portal.maintenance.show', ['maintenanceRequest' => $portalMaintenanceRequest]);
    }

    public function cancel(
        Request $request,
        MaintenanceRequest $portalMaintenanceRequest,
        MaintenanceService $maintenanceService,
    ): RedirectResponse {
        if (! $portalMaintenanceRequest->canBeCancelled()) {
            return back()->with('error', 'This request cannot be cancelled.');
        }

        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $maintenanceService->cancelRequest($portalMaintenanceRequest, $request->user(), $validated['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request cancelled.');
    }
}
