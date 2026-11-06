<?php

namespace App\Http\Controllers\Portal;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TenantPaymentSubmission;
use App\Services\Portal\TenantPaymentSubmissionService;
use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentSubmissionController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $submissions = TenantPaymentSubmission::query()
            ->where('tenant_id', $tenant->id)
            ->with('invoice')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('portal.payment-submissions.index', compact('submissions', 'tenant'));
    }

    public function create(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $openInvoices = Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->orderBy('due_date')
            ->get();

        return view('portal.payment-submissions.create', compact('tenant', 'openInvoices'));
    }

    public function store(
        Request $request,
        TenantPortalContext $tenantPortalContext,
        OrganizationContext $organizationContext,
        TenantPaymentSubmissionService $submissionService,
    ): RedirectResponse {
        $tenant = $tenantPortalContext->ensureTenant();

        $validated = $request->validate([
            'invoice_id' => [
                'nullable',
                'integer',
                Rule::exists('invoices', 'id')->where(fn ($q) => $q->where('tenant_id', $tenant->id)),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference_number' => ['required', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $submissionService->submit(
            $organizationContext->ensureOrganization(),
            $tenant,
            $request->user(),
            $validated,
            $request->file('proof'),
        );

        return redirect()
            ->route('portal.payment-submissions.index')
            ->with('success', 'Payment reference submitted for review.');
    }
}
