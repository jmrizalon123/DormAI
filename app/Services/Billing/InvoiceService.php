<?php

namespace App\Services\Billing;

use App\Enums\ContractStatus;
use App\Enums\InvoiceStatus;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Organization;
use App\Models\Tenant;
use App\Notifications\InvoiceIssuedNotification;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationDispatcher;
use App\Support\Billing\BillingSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly NotificationDispatcher $notificationDispatcher,
    ) {}

    /**
     * @param  array<int, array{item_type?: string, description: string, quantity?: float, unit_price: float}>  $items
     */
    public function createDraft(
        Organization $organization,
        Tenant $tenant,
        array $items,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?Contract $contract = null,
    ): Invoice {
        if ((int) $tenant->organization_id !== (int) $organization->id) {
            throw ValidationException::withMessages(['tenant_id' => ['Invalid tenant for this organization.']]);
        }

        $settings = BillingSettings::forOrganization($organization);
        $dueDay = min((int) $settings['due_day'], $periodEnd->daysInMonth);
        $dueDate = $periodEnd->copy()->day($dueDay);
        if ($dueDate->lessThan(now()->startOfDay())) {
            $dueDate = $dueDate->addMonth()->day(min($dueDay, $dueDate->daysInMonth));
        }

        return DB::transaction(function () use ($organization, $tenant, $items, $periodStart, $periodEnd, $contract, $dueDate) {
            $invoice = Invoice::query()->create([
                'organization_id' => $organization->id,
                'tenant_id' => $tenant->id,
                'contract_id' => $contract?->id,
                'property_id' => $contract?->property_id,
                'invoice_number' => $this->generateInvoiceNumber($organization->id, BillingSettings::forOrganization($organization)['invoice_prefix']),
                'billing_period_start' => $periodStart->toDateString(),
                'billing_period_end' => $periodEnd->toDateString(),
                'issue_date' => now()->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'status' => InvoiceStatus::Draft,
            ]);

            foreach ($items as $row) {
                $quantity = (float) ($row['quantity'] ?? 1);
                $unitPrice = (float) $row['unit_price'];
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $row['item_type'] ?? 'rent',
                    'description' => $row['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => round($quantity * $unitPrice, 2),
                ]);
            }

            $this->recalculateTotals($invoice);
            $this->auditLogger->log('invoice.created', $invoice);

            return $invoice->fresh(['items', 'tenant']);
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw ValidationException::withMessages(['status' => ['Only draft invoices can be issued.']]);
        }

        $invoice->update([
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
            'issue_date' => now()->toDateString(),
        ]);

        $invoice = $invoice->fresh(['tenant.user', 'organization']);
        $this->syncOverdueStatus($invoice);
        $this->auditLogger->log('invoice.issued', $invoice);
        $this->notifyTenantInvoiceIssued($invoice);

        return $invoice->fresh();
    }

    private function notifyTenantInvoiceIssued(Invoice $invoice): void
    {
        $user = $invoice->tenant?->user;
        if (! $user) {
            return;
        }

        $this->notificationDispatcher->notifyUser(
            $user,
            new InvoiceIssuedNotification($invoice),
            'invoice_issued:invoice:'.$invoice->id,
            'invoice_issued',
            $invoice,
        );
    }

    public function void(Invoice $invoice): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void) {
            return $invoice;
        }

        if ((float) $invoice->amount_paid > 0) {
            throw ValidationException::withMessages(['status' => ['Invoices with payments cannot be voided.']]);
        }

        $invoice->update(['status' => InvoiceStatus::Void, 'balance' => 0]);
        $this->auditLogger->log('invoice.voided', $invoice);

        return $invoice->fresh();
    }

    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $subtotal = (float) $invoice->items()->sum('amount');
        $paid = (float) $invoice->allocations()->sum('amount');
        $balance = max(0, round($subtotal - $paid, 2));

        $invoice->update([
            'subtotal' => $subtotal,
            'amount_paid' => $paid,
            'balance' => $balance,
        ]);

        return $this->syncPaymentStatus($invoice->fresh());
    }

    public function syncPaymentStatus(Invoice $invoice): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void || $invoice->status === InvoiceStatus::Draft) {
            return $invoice;
        }

        $balance = (float) $invoice->balance;
        $paid = (float) $invoice->amount_paid;

        if ($balance <= 0 && $paid > 0) {
            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => $invoice->paid_at ?? now(),
                'balance' => 0,
            ]);

            return $invoice->fresh();
        }

        if ($paid > 0 && $balance > 0) {
            $invoice->update([
                'status' => InvoiceStatus::PartiallyPaid,
                'paid_at' => null,
            ]);

            return $this->syncOverdueStatus($invoice->fresh());
        }

        $invoice->update(['status' => InvoiceStatus::Issued, 'paid_at' => null]);

        return $this->syncOverdueStatus($invoice->fresh());
    }

    public function syncOverdueStatus(Invoice $invoice): Invoice
    {
        if (! in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid], true)) {
            return $invoice;
        }

        $settings = BillingSettings::forOrganization($invoice->organization);
        $graceEnd = $invoice->due_date->copy()->addDays((int) $settings['grace_days']);

        if (now()->startOfDay()->greaterThan($graceEnd) && (float) $invoice->balance > 0) {
            $invoice->update(['status' => InvoiceStatus::Overdue]);
        }

        return $invoice->fresh();
    }

    public function markOverdueInvoices(?int $organizationId = null): int
    {
        $count = 0;

        Invoice::query()
            ->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId))
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid])
            ->where('balance', '>', 0)
            ->each(function (Invoice $invoice) use (&$count) {
                $before = $invoice->status;
                $this->syncOverdueStatus($invoice);
                if ($invoice->fresh()->status === InvoiceStatus::Overdue && $before !== InvoiceStatus::Overdue) {
                    $count++;
                }
            });

        return $count;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function generateMonthlyInvoices(Organization $organization, ?Carbon $forMonth = null): Collection
    {
        $settings = BillingSettings::forOrganization($organization);
        if (! $settings['auto_generate_monthly']) {
            return collect();
        }

        $month = ($forMonth ?? now())->copy()->startOfMonth();
        $periodStart = $month->copy();
        $periodEnd = $month->copy()->endOfMonth();

        $created = collect();

        Contract::query()
            ->where('organization_id', $organization->id)
            ->where('status', ContractStatus::Active)
            ->with(['tenant', 'property', 'room'])
            ->each(function (Contract $contract) use ($organization, $periodStart, $periodEnd, &$created) {
                if ($contract->start_date->greaterThan($periodEnd)) {
                    return;
                }
                if ($contract->end_date !== null && $contract->end_date->lessThan($periodStart)) {
                    return;
                }

                $exists = Invoice::query()
                    ->where('organization_id', $organization->id)
                    ->where('tenant_id', $contract->tenant_id)
                    ->whereDate('billing_period_start', $periodStart)
                    ->whereDate('billing_period_end', $periodEnd)
                    ->exists();

                if ($exists) {
                    return;
                }

                $description = sprintf(
                    'Monthly rent — %s · Room %s (%s – %s)',
                    $contract->property->name,
                    $contract->room->room_number,
                    $periodStart->format('M j, Y'),
                    $periodEnd->format('M j, Y'),
                );

                $invoice = $this->createDraft(
                    $organization,
                    $contract->tenant,
                    [[
                        'item_type' => 'rent',
                        'description' => $description,
                        'quantity' => 1,
                        'unit_price' => (float) $contract->monthly_rate,
                    ]],
                    $periodStart,
                    $periodEnd,
                    $contract,
                );

                $this->issue($invoice);
                $created->push($invoice->fresh());
            });

        return $created;
    }

    private function generateInvoiceNumber(int $organizationId, string $prefix): string
    {
        do {
            $number = $prefix.'-'.now()->format('ym').'-'.strtoupper(Str::random(6));
        } while (Invoice::query()->where('organization_id', $organizationId)->where('invoice_number', $number)->exists());

        return $number;
    }
}
