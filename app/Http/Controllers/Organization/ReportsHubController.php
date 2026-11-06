<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\UtilityReading;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportsHubController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewOrganizationReports');

        return view('organization.reports.hub', [
            'canFinancial' => Gate::allows('viewFinancialReports'),
            'canUtilities' => Gate::allows('viewAny', UtilityReading::class),
        ]);
    }
}
