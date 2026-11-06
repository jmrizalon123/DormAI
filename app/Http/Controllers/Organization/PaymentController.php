<?php

namespace App\Http\Controllers\Organization;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\Billing\PaymentService;
use App\Support\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Payment::class);

        $organizationId = $organizationContext->ensureId();

        $payments = Payment::query()
            ->where('organization_id', $organizationId)
            ->with(['tenant', 'recordedBy'])
            ->when($request->filled('tenant_id'), fn ($q) => $q->where('tenant_id', $request->integer('tenant_id')))
            ->orderByDesc('paid_at')
            ->paginate(20)
            ->withQueryString();

        $tenants = Tenant::query()->where('organization_id', $organizationId)->orderBy('last_name')->get();

        return view('organization.billing.payments.index', compact('payments', 'tenants'));
    }

    public function create(Request $request, OrganizationContext $organizationContext, PaymentService $paymentService): View
    {
        $this->authorize('create', Payment::class);

        $organizationId = $organizationContext->ensureId();
        $tenants = Tenant::query()->where('organization_id', $organizationId)->orderBy('last_name')->get();

        $tenant = null;
        $openInvoices = collect();

        if ($request->filled('tenant_id')) {
            $tenant = Tenant::query()
                ->where('organization_id', $organizationId)
                ->whereKey($request->integer('tenant_id'))
                ->first();

            if ($tenant !== null) {
                $openInvoices = $paymentService->openInvoicesForTenant($tenant);
            }
        }

        return view('organization.billing.payments.create', compact('tenants', 'tenant', 'openInvoices'));
    }

    public function store(Request $request, OrganizationContext $organizationContext, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $organization = $organizationContext->organization();

        $validated = $request->validate([
            'tenant_id' => [
                'required',
                'integer',
                Rule::exists('tenants', 'id')->where('organization_id', $organization->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference_number' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'paid_at' => ['nullable', 'date'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.invoice_id' => ['required_with:allocations', 'integer'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $tenant = Tenant::query()->whereKey($validated['tenant_id'])->firstOrFail();

        $allocations = [];
        if (! empty($validated['allocations'])) {
            foreach ($validated['allocations'] as $row) {
                if (! empty($row['amount'])) {
                    $allocations[] = [
                        'invoice_id' => (int) $row['invoice_id'],
                        'amount' => (float) $row['amount'],
                    ];
                }
            }
        }

        $payment = $paymentService->record(
            $organization,
            $tenant,
            (float) $validated['amount'],
            PaymentMethod::from($validated['payment_method']),
            $allocations,
            $request->user(),
            $validated['reference_number'] ?? null,
            $validated['notes'] ?? null,
            isset($validated['paid_at']) ? Carbon::parse($validated['paid_at']) : null,
        );

        return redirect()
            ->route('organization.payments.show', $payment)
            ->with('success', 'Payment recorded.');
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['tenant', 'recordedBy', 'allocations.invoice']);

        return view('organization.billing.payments.show', compact('payment'));
    }

    public function receipt(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['tenant', 'recordedBy', 'allocations.invoice', 'organization']);

        return view('organization.billing.payments.receipt', compact('payment'));
    }
}
