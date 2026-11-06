<?php

namespace App\Services\Finance;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FinancialSummaryService
{
    /**
     * @return array{
     *     income: float,
     *     expenses: float,
     *     net: float,
     *     income_by_method: array<string, float>,
     *     expenses_by_category: array<string, float>
     * }
     */
    public function profitAndLoss(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $organizationId = $organization->id;

        $income = (float) Payment::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        $expenses = (float) Expense::query()
            ->where('organization_id', $organizationId)
            ->where('status', ExpenseStatus::Approved)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $incomeByMethod = Payment::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->map(fn ($v) => (float) $v)
            ->all();

        $expensesByCategory = Expense::query()
            ->where('expenses.organization_id', $organizationId)
            ->where('expenses.status', ExpenseStatus::Approved)
            ->whereBetween('expenses.expense_date', [$from->toDateString(), $to->toDateString()])
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->pluck('total', 'category_name')
            ->map(fn ($v) => (float) $v)
            ->all();

        return [
            'income' => $income,
            'expenses' => $expenses,
            'net' => $income - $expenses,
            'income_by_method' => $incomeByMethod,
            'expenses_by_category' => $expensesByCategory,
        ];
    }

    /**
     * @return Collection<int, array{month: string, income: float, expenses: float, net: float}>
     */
    public function monthlyTrend(Organization $organization, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rows = collect();
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();

        while ($cursor <= $end) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();
            $snapshot = $this->profitAndLoss($organization, $monthStart, $monthEnd);

            $rows->push([
                'month' => $cursor->format('Y-m'),
                'label' => $cursor->format('M Y'),
                'income' => $snapshot['income'],
                'expenses' => $snapshot['expenses'],
                'net' => $snapshot['net'],
            ]);

            $cursor->addMonth();
        }

        return $rows;
    }

    /**
     * @return array{pending_count: int, pending_amount: float, approved_mtd: float}
     */
    public function expenseDashboard(Organization $organization): array
    {
        $now = now();

        return [
            'pending_count' => Expense::query()
                ->where('organization_id', $organization->id)
                ->where('status', ExpenseStatus::PendingApproval)
                ->count(),
            'pending_amount' => (float) Expense::query()
                ->where('organization_id', $organization->id)
                ->where('status', ExpenseStatus::PendingApproval)
                ->sum('amount'),
            'approved_mtd' => (float) Expense::query()
                ->where('organization_id', $organization->id)
                ->where('status', ExpenseStatus::Approved)
                ->whereBetween('expense_date', [$now->copy()->startOfMonth()->toDateString(), $now->toDateString()])
                ->sum('amount'),
        ];
    }
}
