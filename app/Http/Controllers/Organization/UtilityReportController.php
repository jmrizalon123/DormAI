<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\UtilityReading;
use App\Services\Utilities\UtilityReportService;
use App\Support\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilityReportController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext, UtilityReportService $reportService): View
    {
        $this->authorize('viewAny', UtilityReading::class);

        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $summary = $reportService->consumptionSummary($organizationContext->ensureOrganization(), $from, $to);

        return view('organization.utilities.reports.index', [
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'exportReport' => 'utility',
        ]);
    }
}
