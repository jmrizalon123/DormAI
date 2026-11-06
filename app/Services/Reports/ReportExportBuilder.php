<?php

namespace App\Services\Reports;

use App\Enums\ReservationStatus;
use App\Models\Organization;
use App\Models\UtilityReading;
use App\Services\Finance\FinancialSummaryService;
use App\Services\Utilities\UtilityReportService;
use App\Support\Reports\ReportDateRange;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ReportExportBuilder
{
    public function __construct(
        private OccupancyReportService $occupancyReportService,
        private PaymentCollectionReportService $paymentCollectionReportService,
        private TenantReportService $tenantReportService,
        private ReservationReportService $reservationReportService,
        private ExpenseReportService $expenseReportService,
        private FinancialSummaryService $financialSummaryService,
        private UtilityReportService $utilityReportService,
    ) {}

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    public function build(string $report, Organization $organization, ReportDateRange $range): array
    {
        return match ($report) {
            'occupancy' => $this->occupancy($organization),
            'payment-collection' => $this->paymentCollection($organization, $range),
            'tenants' => $this->tenants($organization, $range),
            'reservations' => $this->reservations($organization, $range),
            'expenses' => $this->expenses($organization, $range),
            'financial-summary' => $this->financialSummary($organization, $range),
            'utility' => $this->utility($organization, $range),
            default => throw new \InvalidArgumentException('Unknown report type.'),
        };
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function occupancy(Organization $organization): array
    {
        $data = $this->occupancyReportService->build($organization);
        $summary = $data['summary'];

        $rows = [
            [
                'All properties',
                '—',
                $summary['total'],
                $summary['occupied'],
                $summary['available'],
                $summary['reserved'],
                $summary['rate'] !== null ? $summary['rate'].'%' : '—',
            ],
        ];

        foreach ($data['by_property'] as $property) {
            $rows[] = [
                $property['property'],
                $property['rooms'],
                $property['bed_spaces'],
                $property['occupied'],
                $property['available'],
                '—',
                $property['occupancy_rate'] !== null ? $property['occupancy_rate'].'%' : '—',
            ];
        }

        return [
            'title' => 'Occupancy report',
            'subtitle' => 'Snapshot as of '.now()->format('M j, Y'),
            'headers' => ['Property', 'Rooms', 'Bed spaces', 'Occupied', 'Available', 'Reserved', 'Occupancy rate'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function paymentCollection(Organization $organization, ReportDateRange $range): array
    {
        $data = $this->paymentCollectionReportService->build($organization, $range->from, $range->to);

        $rows = [
            ['Summary', 'Invoiced (period)', number_format($data['invoiced'], 2, '.', ''), ''],
            ['Summary', 'Collected (period)', number_format($data['collected'], 2, '.', ''), ''],
            ['Summary', 'Outstanding (open invoices)', number_format($data['outstanding'], 2, '.', ''), ''],
            ['Summary', 'Collection rate', $data['collection_rate'] !== null ? $data['collection_rate'].'%' : '—', ''],
        ];

        foreach ($data['monthly'] as $month) {
            $rows[] = [
                'Monthly',
                $month['label'],
                number_format($month['invoiced'], 2, '.', ''),
                number_format($month['collected'], 2, '.', ''),
            ];
        }

        return [
            'title' => 'Payment collection report',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Section', 'Period / metric', 'Invoiced', 'Collected'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function tenants(Organization $organization, ReportDateRange $range): array
    {
        $data = $this->tenantReportService->build($organization, $range->from, $range->to);

        return [
            'title' => 'Tenant report',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Metric', 'Value'],
            'rows' => [
                ['Total tenant records', $data['total_tenants']],
                ['Active bed assignments', $data['active_assignments']],
                ['Move-ins (period)', $data['move_ins']],
                ['Move-outs (period)', $data['move_outs']],
            ],
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function reservations(Organization $organization, ReportDateRange $range): array
    {
        $data = $this->reservationReportService->build($organization, $range->from, $range->to);

        $rows = [
            ['Total requests (period)', $data['total']],
            ['Check-in conversion', $data['conversion_rate'] !== null ? $data['conversion_rate'].'%' : '—'],
        ];

        foreach (ReservationStatus::cases() as $status) {
            $rows[] = [$status->label(), $data['by_status'][$status->value] ?? 0];
        }

        return [
            'title' => 'Reservation report',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Status / metric', 'Count'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function expenses(Organization $organization, ReportDateRange $range): array
    {
        $data = $this->expenseReportService->build($organization, $range->from, $range->to);

        $rows = [
            ['Summary', 'Approved total (period)', '', number_format($data['approved_total'], 2, '.', '')],
            ['Summary', 'Pending approval', $data['pending_count'].' items', number_format($data['pending_amount'], 2, '.', '')],
        ];

        foreach ($data['by_category'] as $category => $total) {
            $rows[] = ['By category', $category, '', number_format($total, 2, '.', '')];
        }

        foreach ($data['recent'] as $expense) {
            $rows[] = [
                'Expense',
                $expense->expense_date->format('Y-m-d'),
                trim(($expense->category?->name ?? 'Uncategorized').': '.$expense->description),
                number_format((float) $expense->amount, 2, '.', ''),
            ];
        }

        return [
            'title' => 'Expense report',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Section', 'Date / category', 'Description', 'Amount'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function financialSummary(Organization $organization, ReportDateRange $range): array
    {
        if (! Gate::allows('viewFinancialReports')) {
            throw new AuthorizationException;
        }

        $report = $this->financialSummaryService->profitAndLoss($organization, $range->from, $range->to);
        $trend = $this->financialSummaryService->monthlyTrend(
            $organization,
            $range->from->copy()->startOfMonth(),
            $range->to,
        );

        $rows = [
            ['Summary', 'Income', number_format($report['income'], 2, '.', ''), '', ''],
            ['Summary', 'Expenses', number_format($report['expenses'], 2, '.', ''), '', ''],
            ['Summary', 'Net', number_format($report['net'], 2, '.', ''), '', ''],
        ];

        foreach ($trend as $row) {
            $rows[] = [
                'Trend',
                $row['label'],
                number_format($row['income'], 2, '.', ''),
                number_format($row['expenses'], 2, '.', ''),
                number_format($row['net'], 2, '.', ''),
            ];
        }

        return [
            'title' => 'Financial summary',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Section', 'Period / metric', 'Income', 'Expenses', 'Net'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    private function utility(Organization $organization, ReportDateRange $range): array
    {
        if (! Gate::allows('viewAny', UtilityReading::class)) {
            throw new AuthorizationException;
        }

        $summary = $this->utilityReportService->consumptionSummary($organization, $range->from, $range->to);

        $rows = [
            ['Summary', 'Readings in period', (string) $summary['readings_count'], '', ''],
            ['Summary', 'Total billed', '', '', number_format($summary['total_billed'], 2, '.', '')],
        ];

        foreach ($summary['by_property'] as $property) {
            $rows[] = [
                'Property',
                $property['property'],
                (string) $property['readings'],
                number_format($property['consumption'], 2, '.', ''),
                number_format($property['billed'], 2, '.', ''),
            ];
        }

        return [
            'title' => 'Utility report',
            'subtitle' => $range->from->format('M j, Y').' – '.$range->to->format('M j, Y'),
            'headers' => ['Section', 'Property', 'Readings', 'Consumption', 'Billed'],
            'rows' => $rows,
        ];
    }
}
