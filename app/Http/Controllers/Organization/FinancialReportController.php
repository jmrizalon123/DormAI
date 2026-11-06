<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinancialSummaryService;
use App\Support\Reports\ReportDateRange;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function summary(Request $request, OrganizationContext $organizationContext, FinancialSummaryService $summaryService): View
    {
        Gate::authorize('viewFinancialReports');

        $organization = $organizationContext->ensureOrganization();
        $range = ReportDateRange::fromRequest($request);
        $from = $range->from;
        $to = $range->to;

        $report = $summaryService->profitAndLoss($organization, $from, $to);
        $trend = $summaryService->monthlyTrend($organization, $from->copy()->startOfMonth(), $to);
        $expenseStats = $summaryService->expenseDashboard($organization);

        return view('organization.finance.reports.summary', [
            'report' => $report,
            'trend' => $trend,
            'expenseStats' => $expenseStats,
            'from' => $from,
            'to' => $to,
            'exportReport' => 'financial-summary',
        ]);
    }

    public function profitLoss(Request $request, OrganizationContext $organizationContext, FinancialSummaryService $summaryService): View
    {
        Gate::authorize('viewFinancialReports');

        $organization = $organizationContext->ensureOrganization();
        $range = ReportDateRange::fromRequest($request);
        $from = $range->from;
        $to = $range->to;

        $report = $summaryService->profitAndLoss($organization, $from, $to);

        return view('organization.finance.reports.profit-loss', compact('report', 'from', 'to'));
    }

    public function incomeExpense(Request $request, OrganizationContext $organizationContext, FinancialSummaryService $summaryService): View
    {
        Gate::authorize('viewFinancialReports');

        $organization = $organizationContext->ensureOrganization();
        $range = ReportDateRange::fromRequest($request);
        $from = $range->from;
        $to = $range->to;

        $report = $summaryService->profitAndLoss($organization, $from, $to);
        $trend = $summaryService->monthlyTrend($organization, $from->copy()->startOfMonth(), $to);

        return view('organization.finance.reports.income-expense', [
            'report' => $report,
            'trend' => $trend,
            'from' => $from,
            'to' => $to,
            'exportReport' => 'financial-summary',
        ]);
    }
}
