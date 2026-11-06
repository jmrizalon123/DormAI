<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceLog;
use App\Models\WorkOrder;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceHistoryController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', WorkOrder::class);

        $organizationId = $organizationContext->ensureId();

        $logs = MaintenanceLog::query()
            ->where('organization_id', $organizationId)
            ->with(['user', 'workOrder', 'maintenanceRequest'])
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'open' => WorkOrder::query()->where('organization_id', $organizationId)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed_mtd' => WorkOrder::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfMonth())
                ->count(),
            'cost_mtd' => (float) WorkOrder::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfMonth())
                ->sum('total_cost'),
        ];

        return view('organization.maintenance.history.index', compact('logs', 'stats'));
    }
}
