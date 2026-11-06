<?php

namespace App\Http\Controllers\Organization;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Services\Reservation\CheckInService;
use App\Services\Reservation\ReservationService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Reservation::class);

        $organizationId = $organizationContext->ensureId();

        $reservations = Reservation::query()
            ->where('organization_id', $organizationId)
            ->with(['property', 'room', 'bedSpace', 'tenant'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_code', 'like', "%{$search}%")
                        ->orWhere('applicant_first_name', 'like', "%{$search}%")
                        ->orWhere('applicant_last_name', 'like', "%{$search}%")
                        ->orWhere('applicant_email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('organization.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation): View
    {
        $this->authorize('view', $reservation);

        $reservation->load(['property', 'room', 'bedSpace', 'tenant', 'contract', 'items', 'approvedBy']);

        $tenants = Tenant::query()
            ->where('organization_id', $reservation->organization_id)
            ->orderBy('last_name')
            ->limit(50)
            ->get();

        return view('organization.reservations.show', compact('reservation', 'tenants'));
    }

    public function approve(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $reservationService->approve($reservation, request()->user());

        return back()->with('success', 'Reservation approved.');
    }

    public function reject(Request $request, Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $reservationService->reject($reservation, $request->user(), $validated['rejection_reason']);

        return back()->with('success', 'Reservation rejected.');
    }

    public function markDepositPaid(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $reservationService->markDepositPaid($reservation);

        return back()->with('success', 'Deposit marked as paid.');
    }

    public function checkIn(Request $request, Reservation $reservation, CheckInService $checkInService): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $validated = $request->validate([
            'tenant_id' => [
                'nullable',
                'integer',
                Rule::exists('tenants', 'id')->where('organization_id', $reservation->organization_id),
            ],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'advance_payment' => ['nullable', 'numeric', 'min:0'],
        ]);

        $overrides = array_filter([
            'monthly_rate' => $validated['monthly_rate'] ?? null,
            'security_deposit' => $validated['security_deposit'] ?? null,
            'advance_payment' => $validated['advance_payment'] ?? null,
        ], fn ($v) => $v !== null);

        $result = $checkInService->checkIn(
            $reservation,
            $validated['tenant_id'] ?? null,
            $overrides,
        );

        return redirect()
            ->route('organization.contracts.show', $result['contract'])
            ->with('success', 'Guest checked in and contract created.');
    }

    public function cancel(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $this->authorize('update', $reservation);

        if (! in_array($reservation->status, [ReservationStatus::Pending, ReservationStatus::Approved], true)) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservationService->cancel($reservation);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function updateStaffNotes(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('update', $reservation);

        $validated = $request->validate([
            'staff_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $reservation->update(['staff_notes' => $validated['staff_notes']]);

        return back()->with('success', 'Staff notes saved.');
    }
}
