<?php

namespace App\Services\Utilities;

use App\Enums\UtilityAllocationStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\UtilityAllocation;
use App\Models\UtilityReading;
use App\Services\Billing\InvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UtilityBillingService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * @return Collection<int, Invoice>
     */
    public function createDraftInvoicesForReading(UtilityReading $reading, Organization $organization): Collection
    {
        $reading->load(['allocations.tenant', 'allocations.room', 'meter']);

        $pending = $reading->allocations()
            ->where('status', UtilityAllocationStatus::Calculated)
            ->whereNotNull('tenant_id')
            ->get();

        if ($pending->isEmpty()) {
            throw new InvalidArgumentException('No billable allocations found. Run allocation first.');
        }

        return DB::transaction(function () use ($pending, $reading, $organization) {
            $invoices = collect();

            foreach ($pending->groupBy('tenant_id') as $tenantId => $rows) {
                /** @var UtilityAllocation $first */
                $first = $rows->first();
                $tenant = $first->tenant;
                if ($tenant === null) {
                    continue;
                }

                $periodStart = Carbon::parse($reading->reading_date)->startOfMonth();
                $periodEnd = Carbon::parse($reading->reading_date)->endOfMonth();

                $items = $rows->map(function (UtilityAllocation $row) use ($reading) {
                    $type = $reading->meter->utility_type->label();

                    return [
                        'item_type' => 'utility',
                        'description' => "{$type} · Room {$row->room->room_number} · {$reading->reading_date->format('M j, Y')}",
                        'quantity' => (float) $row->consumption_units,
                        'unit_price' => (float) $row->rate_per_unit,
                    ];
                })->values()->all();

                $invoice = $this->invoiceService->createDraft(
                    $organization,
                    $tenant,
                    $items,
                    $periodStart,
                    $periodEnd,
                );

                UtilityAllocation::query()
                    ->whereIn('id', $rows->pluck('id'))
                    ->update([
                        'status' => UtilityAllocationStatus::Invoiced,
                        'invoice_id' => $invoice->id,
                    ]);

                $invoices->push($invoice);
            }

            return $invoices;
        });
    }
}
