<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request, TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $invoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('issue_date')
            ->paginate(15)
            ->withQueryString();

        return view('portal.invoices.index', compact('invoices', 'tenant'));
    }

    public function show(Invoice $portalInvoice): View
    {
        $portalInvoice->load(['items', 'property', 'allocations.payment']);

        return view('portal.invoices.show', ['invoice' => $portalInvoice]);
    }
}
