<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ContractStatus;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant()->load([
            'activeAssignment.property',
            'activeAssignment.room',
            'activeAssignment.bedSpace',
        ]);

        $organizationId = $tenant->organization_id;

        $stats = [
            'balance' => (float) Invoice::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
                ->sum('balance'),
            'open_requests' => MaintenanceRequest::query()
                ->where('tenant_id', $tenant->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];

        $announcements = Announcement::query()
            ->where('organization_id', $organizationId)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $activeContract = Contract::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', ContractStatus::Active)
            ->latest('start_date')
            ->first();

        return view('portal.dashboard', compact('tenant', 'stats', 'announcements', 'activeContract'));
    }
}
