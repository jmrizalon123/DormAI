<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $payments = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->with(['allocations.invoice'])
            ->orderByDesc('paid_at')
            ->paginate(15);

        return view('portal.payments.index', compact('payments', 'tenant'));
    }
}
