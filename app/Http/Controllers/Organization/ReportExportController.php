<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportExportBuilder;
use App\Support\Reports\CsvExporter;
use App\Support\Reports\ReportDateRange;
use App\Support\Tenancy\OrganizationContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    private const ALLOWED = [
        'occupancy',
        'payment-collection',
        'tenants',
        'reservations',
        'expenses',
        'financial-summary',
        'utility',
    ];

    public function __invoke(
        Request $request,
        string $report,
        OrganizationContext $organizationContext,
        ReportExportBuilder $exportBuilder,
    ): Response {
        Gate::authorize('viewOrganizationReports');

        if (! in_array($report, self::ALLOWED, true)) {
            abort(404);
        }

        $format = $request->string('format')->lower()->toString();
        if (! in_array($format, ['csv', 'pdf'], true)) {
            abort(422, 'Invalid export format.');
        }

        $organization = $organizationContext->ensureOrganization();
        $range = ReportDateRange::fromRequest($request);
        $payload = $exportBuilder->build($report, $organization, $range);

        $slug = Str::slug($organization->name);
        $basename = $slug.'-'.$report.'-'.now()->format('Ymd');

        if ($format === 'csv') {
            return CsvExporter::download($basename.'.csv', $payload['headers'], $payload['rows']);
        }

        $pdf = Pdf::loadView('organization.reports.pdf.table', $payload)
            ->setPaper('a4', 'portrait');

        return $pdf->download($basename.'.pdf');
    }
}
