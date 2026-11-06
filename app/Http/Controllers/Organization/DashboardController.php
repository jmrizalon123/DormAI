<?php

namespace App\Http\Controllers\Organization;

use App\Enums\ReservationStatus;
use App\Enums\TenantAssignmentStatus;
use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Services\Dashboard\DashboardAnalyticsService;
use App\Services\Property\OccupancyService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        OrganizationContext $organizationContext,
        OccupancyService $occupancyService,
        DashboardAnalyticsService $dashboardAnalytics,
    ): View {
        $organization = $organizationContext->organization();
        $organizationId = $organizationContext->ensureId();

        $bedSpaces = BedSpace::query()->where('organization_id', $organizationId)->get();
        $bedSummary = $occupancyService->summarizeBedSpaces($bedSpaces);

        $stats = [
            'properties_total' => Property::query()->where('organization_id', $organizationId)->count(),
            'rooms_total' => Room::query()->where('organization_id', $organizationId)->count(),
            'bed_spaces_total' => $bedSummary['total'],
            'bed_spaces_available' => $bedSummary['available'],
            'occupancy_rate' => $bedSummary['rate'],
            'active_tenants' => TenantAssignment::query()
                ->where('organization_id', $organizationId)
                ->where('status', TenantAssignmentStatus::Active)
                ->count(),
            'pending_reservations' => Reservation::query()
                ->where('organization_id', $organizationId)
                ->where('status', ReservationStatus::Pending)
                ->count(),
            'users_total' => User::query()->where('organization_id', $organizationId)->count(),
        ];

        $analytics = $dashboardAnalytics->build($organization, $stats, $bedSummary);

        return view('organization.dashboard', [
            'organization' => $organization,
            'stats' => $stats,
            'bedSummary' => $bedSummary,
            'chartCards' => $analytics['cards'],
            'featuredCharts' => $analytics['featured'],
        ]);
    }
}
