<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Services\Reservation\ReservationService;
use App\Support\Routing\PublicOrganizationBinding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationRequestController extends Controller
{
    public function store(
        Request $request,
        string $publicOrganization,
        BedSpace $bedSpace,
        ReservationService $reservationService,
    ): RedirectResponse {
        $organization = PublicOrganizationBinding::resolve($publicOrganization);

        abort_unless((int) $bedSpace->organization_id === (int) $organization->id, 404);

        $validated = $request->validate([
            'applicant_first_name' => ['required', 'string', 'max:100'],
            'applicant_last_name' => ['required', 'string', 'max:100'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:30'],
            'requested_check_in' => ['required', 'date', 'after_or_equal:today'],
            'requested_check_out' => ['nullable', 'date', 'after:requested_check_in'],
            'applicant_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $reservation = $reservationService->submitPublicRequest($organization, $bedSpace, $validated);

        return redirect()
            ->route('public.stay.confirmation', [
                'publicOrganization' => $organization->slug,
                'reference' => $reservation->reference_code,
            ])
            ->with('success', 'Your reservation request was submitted.');
    }

    public function confirmation(string $publicOrganization, string $reference): View
    {
        $organization = PublicOrganizationBinding::resolve($publicOrganization);

        $reservation = $organization->reservations()
            ->where('reference_code', $reference)
            ->with(['property', 'room', 'bedSpace'])
            ->firstOrFail();

        return view('public.stay.confirmation', [
            'organization' => $organization,
            'reservation' => $reservation,
        ]);
    }
}
