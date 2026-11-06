<?php

namespace App\Services\Reports;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\Organization;
use App\Services\Finance\FinancialSummaryService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ExpenseReportService
{
    public function __construct(private FinancialSummaryService $financialSummaryService) {}

    /**
     * @return array{
     *     approved_total: float,
     *     pending_count: int,
     *     pending_amount: float,
     *     by_category: array<string, float>,
     *     recent: Collection<int, Expense>
     * }
     */
    public function build(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $organizationId = $organization->id;
        $snapshot = $this->financialSummaryService->profitAndLoss($organization, $from, $to);
        $dashboard = $this->financialSummaryService->expenseDashboard($organization);

        $recent = Expense::query()
            ->where('organization_id', $organizationId)
            ->where('status', ExpenseStatus::Approved)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->with('category')
            ->orderByDesc('expense_date')
            ->limit(50)
            ->get();

        return [
            'approved_total' => $snapshot['expenses'],
            'pending_count' => $dashboard['pending_count'],
            'pending_amount' => $dashboard['pending_amount'],
            'by_category' => $snapshot['expenses_by_category'],
            'recent' => $recent,
        ];
    }
}
