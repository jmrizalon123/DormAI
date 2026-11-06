<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Reports\ExpenseReportService;
use App\Services\Reports\OccupancyReportService;
use App\Services\Reports\PaymentCollectionReportService;
use App\Services\Reports\ReservationReportService;
use App\Services\Reports\TenantReportService;
use App\Support\Reports\ReportDateRange;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AnalyticsReportController extends Controller
{
    public function occupancy(
        OrganizationContext $organizationContext,
        OccupancyReportService $reportService,
    ): View {
        Gate::authorize('viewOrganizationReports');

        $organization = $organizationContext->ensureOrganization();
        $report = $reportService->build($organization);

        return view('organization.reports.occupancy', [
            'report' => $report,
            'from' => now()->startOfMonth(),
            'to' => now(),
            'exportReport' => 'occupancy',
        ]);
    }

    public function paymentCollection(
        Request $request,
        OrganizationContext $organizationContext,
        PaymentCollectionReportService $reportService,
    ): View {
        Gate::authorize('viewOrganizationReports');

        $range = ReportDateRange::fromRequest($request);
        $organization = $organizationContext->ensureOrganization();
        $report = $reportService->build($organization, $range->from, $range->to);

        return view('organization.reports.payment-collection', [
            'report' => $report,
            'from' => $range->from,
            'to' => $range->to,
            'exportReport' => 'payment-collection',
        ]);
    }

    public function tenants(
        Request $request,
        OrganizationContext $organizationContext,
        TenantReportService $reportService,
    ): View {
        Gate::authorize('viewOrganizationReports');

        $range = ReportDateRange::fromRequest($request);
        $organization = $organizationContext->ensureOrganization();
        $report = $reportService->build($organization, $range->from, $range->to);

        return view('organization.reports.tenants', [
            'report' => $report,
            'from' => $range->from,
            'to' => $range->to,
            'exportReport' => 'tenants',
        ]);
    }

    public function reservations(
        Request $request,
        OrganizationContext $organizationContext,
        ReservationReportService $reportService,
    ): View {
        Gate::authorize('viewOrganizationReports');

        $range = ReportDateRange::fromRequest($request);
        $organization = $organizationContext->ensureOrganization();
        $report = $reportService->build($organization, $range->from, $range->to);

        return view('organization.reports.reservations', [
            'report' => $report,
            'from' => $range->from,
            'to' => $range->to,
            'exportReport' => 'reservations',
        ]);
    }

    public function expenses(
        Request $request,
        OrganizationContext $organizationContext,
        ExpenseReportService $reportService,
    ): View {
        Gate::authorize('viewOrganizationReports');

        $range = ReportDateRange::fromRequest($request);
        $organization = $organizationContext->ensureOrganization();
        $report = $reportService->build($organization, $range->from, $range->to);

        return view('organization.reports.expenses', [
            'report' => $report,
            'from' => $range->from,
            'to' => $range->to,
            'exportReport' => 'expenses',
        ]);
    }
}
