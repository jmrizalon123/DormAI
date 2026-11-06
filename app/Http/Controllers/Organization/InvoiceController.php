<?php

namespace App\Http\Controllers\Organization;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\Billing\InvoiceService;
use App\Support\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Invoice::class);

        $organizationId = $organizationContext->ensureId();

        $invoices = Invoice::query()
            ->where('organization_id', $organizationId)
            ->with(['tenant', 'property'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($t) => $t->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('issue_date')
            ->paginate(20)
            ->withQueryString();

        $outstanding = (float) Invoice::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->sum('balance');

        return view('organization.billing.invoices.index', compact('invoices', 'outstanding'));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['tenant', 'property', 'contract', 'items', 'allocations.payment']);

        return view('organization.billing.invoices.show', compact('invoice'));
    }

    public function create(OrganizationContext $organizationContext): View
    {
        $this->authorize('create', Invoice::class);

        $tenants = Tenant::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->orderBy('last_name')
            ->get();

        return view('organization.billing.invoices.create', compact('tenants'));
    }

    public function store(Request $request, OrganizationContext $organizationContext, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'billing_period_start' => ['required', 'date'],
            'billing_period_end' => ['required', 'date', 'after_or_equal:billing_period_start'],
            'description' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'issue_now' => ['sometimes', 'boolean'],
        ]);

        $organization = $organizationContext->organization();
        $tenant = Tenant::query()
            ->where('organization_id', $organization->id)
            ->whereKey($validated['tenant_id'])
            ->firstOrFail();

        $invoice = $invoiceService->createDraft(
            $organization,
            $tenant,
            [[
                'description' => $validated['description'],
                'unit_price' => (float) $validated['unit_price'],
            ]],
            Carbon::parse($validated['billing_period_start']),
            Carbon::parse($validated['billing_period_end']),
        );

        if ($request->boolean('issue_now')) {
            $invoiceService->issue($invoice);
        }

        return redirect()
            ->route('organization.invoices.show', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function issue(Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $invoiceService->issue($invoice);

        return back()->with('success', 'Invoice issued.');
    }

    public function void(Invoice $invoice, InvoiceService $invoiceService): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $invoiceService->void($invoice);

        return back()->with('success', 'Invoice voided.');
    }

    public function print(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['tenant', 'property', 'items', 'organization']);

        return view('organization.billing.invoices.print', compact('invoice'));
    }
}
