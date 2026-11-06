<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<int, array{invoice_id: int, amount: float}>  $allocations
     */
    public function record(
        Organization $organization,
        Tenant $tenant,
        float $amount,
        PaymentMethod $method,
        array $allocations,
        User $recordedBy,
        ?string $referenceNumber = null,
        ?string $notes = null,
        ?\DateTimeInterface $paidAt = null,
    ): Payment {
        if ((int) $tenant->organization_id !== (int) $organization->id) {
            throw ValidationException::withMessages(['tenant_id' => ['Invalid tenant for this organization.']]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Payment amount must be greater than zero.']]);
        }

        if ($allocations === []) {
            $allocations = $this->suggestFifoAllocations($organization, $tenant, $amount);
        }

        $allocationTotal = round(array_sum(array_column($allocations, 'amount')), 2);
        if ($allocationTotal > round($amount, 2)) {
            throw ValidationException::withMessages(['allocations' => ['Allocated amount cannot exceed payment amount.']]);
        }

        return DB::transaction(function () use ($organization, $tenant, $amount, $method, $allocations, $recordedBy, $referenceNumber, $notes, $paidAt) {
            $payment = Payment::query()->create([
                'organization_id' => $organization->id,
                'tenant_id' => $tenant->id,
                'payment_number' => $this->generatePaymentNumber($organization->id),
                'paid_at' => $paidAt ?? now(),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'recorded_by' => $recordedBy->id,
            ]);

            foreach ($allocations as $row) {
                $invoice = Invoice::query()
                    ->where('organization_id', $organization->id)
                    ->where('tenant_id', $tenant->id)
                    ->whereKey($row['invoice_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($invoice->status === InvoiceStatus::Void || $invoice->status === InvoiceStatus::Draft) {
                    throw ValidationException::withMessages([
                        'allocations' => ["Invoice {$invoice->invoice_number} cannot receive payments."],
                    ]);
                }

                $allocateAmount = round((float) $row['amount'], 2);
                if ($allocateAmount <= 0) {
                    continue;
                }

                if ($allocateAmount > (float) $invoice->balance) {
                    throw ValidationException::withMessages([
                        'allocations' => ["Allocation exceeds balance for invoice {$invoice->invoice_number}."],
                    ]);
                }

                PaymentAllocation::query()->create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allocateAmount,
                ]);

                $this->invoiceService->recalculateTotals($invoice->fresh(['items']));
            }

            $this->auditLogger->log('payment.recorded', $payment, [
                'tenant_id' => $tenant->id,
                'amount' => $amount,
            ]);

            return $payment->fresh(['allocations.invoice', 'tenant', 'recordedBy']);
        });
    }

    /**
     * @return array<int, array{invoice_id: int, amount: float}>
     */
    private function suggestFifoAllocations(Organization $organization, Tenant $tenant, float $amount): array
    {
        $remaining = round($amount, 2);
        $allocations = [];

        $invoices = Invoice::query()
            ->where('organization_id', $organization->id)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [
                InvoiceStatus::Issued,
                InvoiceStatus::PartiallyPaid,
                InvoiceStatus::Overdue,
            ])
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        foreach ($invoices as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $apply = min($remaining, (float) $invoice->balance);
            $allocations[] = [
                'invoice_id' => $invoice->id,
                'amount' => $apply,
            ];
            $remaining = round($remaining - $apply, 2);
        }

        if ($allocations === []) {
            throw ValidationException::withMessages([
                'allocations' => ['No open invoices found for this tenant. Add manual allocations or create an invoice first.'],
            ]);
        }

        return $allocations;
    }

    private function generatePaymentNumber(int $organizationId): string
    {
        do {
            $number = 'PAY-'.now()->format('ym').'-'.strtoupper(Str::random(6));
        } while (Payment::query()->where('organization_id', $organizationId)->where('payment_number', $number)->exists());

        return $number;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function openInvoicesForTenant(Tenant $tenant): Collection
    {
        return Invoice::query()
            ->where('organization_id', $tenant->organization_id)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [
                InvoiceStatus::Issued,
                InvoiceStatus::PartiallyPaid,
                InvoiceStatus::Overdue,
            ])
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->get();
    }
}
