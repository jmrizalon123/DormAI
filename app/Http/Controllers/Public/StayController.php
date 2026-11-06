<?php

namespace App\Http\Controllers\Public;

use App\Enums\BedSpaceStatus;
use App\Enums\ReservationStatus;
use App\Enums\TenantAssignmentStatus;
use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Models\Reservation;
use App\Support\Routing\PublicOrganizationBinding;
use Illuminate\View\View;

class StayController extends Controller
{
    public function index(string $publicOrganization): View
    {
        $organization = PublicOrganizationBinding::resolve($publicOrganization);

        $beds = BedSpace::query()
            ->where('organization_id', $organization->id)
            ->where('status', BedSpaceStatus::Available)
            ->whereDoesntHave('assignments', fn ($q) => $q->where('status', TenantAssignmentStatus::Active))
            ->whereNotIn('id', Reservation::query()
                ->select('bed_space_id')
                ->where('organization_id', $organization->id)
                ->whereIn('status', [ReservationStatus::Pending, ReservationStatus::Approved]))
            ->with(['room.property', 'room.floor', 'room.building'])
            ->orderBy('label')
            ->get();

        return view('public.stay.index', [
            'organization' => $organization,
            'beds' => $beds,
        ]);
    }

    public function show(string $publicOrganization, BedSpace $bedSpace): View
    {
        $organization = PublicOrganizationBinding::resolve($publicOrganization);

        abort_unless((int) $bedSpace->organization_id === (int) $organization->id, 404);
        abort_unless($bedSpace->status === BedSpaceStatus::Available, 404);

        $bedSpace->load(['room.property', 'room.building', 'room.floor']);

        return view('public.stay.reserve', [
            'organization' => $organization,
            'bedSpace' => $bedSpace,
        ]);
    }
}
