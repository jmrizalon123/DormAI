<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PaymentCollectionReportService
{
    /**
     * @return array{
     *     invoiced: float,
     *     collected: float,
     *     outstanding: float,
     *     collection_rate: float|null,
     *     invoices_by_status: array<string, array{count: int, balance: float}>,
     *     monthly: Collection<int, array{label: string, invoiced: float, collected: float}>
     * }
     */
    public function build(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $organizationId = $organization->id;

        $invoiced = (float) Invoice::query()
            ->where('organization_id', $organizationId)
            ->whereNotIn('status', [InvoiceStatus::Draft, InvoiceStatus::Void])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->sum('subtotal');

        $collected = (float) Payment::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        $openStatuses = [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue];

        $outstanding = (float) Invoice::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', $openStatuses)
            ->sum('balance');

        $collectionRate = $invoiced > 0 ? round(($collected / $invoiced) * 100, 1) : null;

        $invoicesByStatus = [];
        foreach (InvoiceStatus::cases() as $status) {
            if (in_array($status, [InvoiceStatus::Draft, InvoiceStatus::Void], true)) {
                continue;
            }

            $query = Invoice::query()
                ->where('organization_id', $organizationId)
                ->where('status', $status);

            $invoicesByStatus[$status->value] = [
                'count' => $query->count(),
                'balance' => (float) (clone $query)->sum('balance'),
            ];
        }

        $monthly = $this->monthlyTrend($organization, $from, $to);

        return [
            'invoiced' => $invoiced,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'collection_rate' => $collectionRate,
            'invoices_by_status' => $invoicesByStatus,
            'monthly' => $monthly,
        ];
    }

    /**
     * @return Collection<int, array{label: string, invoiced: float, collected: float}>
     */
    private function monthlyTrend(Organization $organization, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rows = collect();
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();
        $organizationId = $organization->id;

        while ($cursor <= $end) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $invoiced = (float) Invoice::query()
                ->where('organization_id', $organizationId)
                ->whereNotIn('status', [InvoiceStatus::Draft, InvoiceStatus::Void])
                ->whereBetween('issue_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('subtotal');

            $collected = (float) Payment::query()
                ->where('organization_id', $organizationId)
                ->whereBetween('paid_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                ->sum('amount');

            $rows->push([
                'label' => $cursor->format('M Y'),
                'invoiced' => $invoiced,
                'collected' => $collected,
            ]);

            $cursor->addMonth();
        }

        return $rows;
    }
}
