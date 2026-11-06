<?php

namespace App\Services\Reservation;

use App\Enums\ReservationStatus;
use App\Enums\TenantStatus;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Services\Contract\ContractService;
use App\Services\Tenant\TenantAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    public function __construct(
        private readonly TenantAssignmentService $assignmentService,
        private readonly ContractService $contractService,
    ) {}

    /**
     * @param  array<string, mixed>  $contractOverrides
     * @return array{tenant: Tenant, contract: Contract, reservation: Reservation}
     */
    public function checkIn(Reservation $reservation, ?int $tenantId = null, array $contractOverrides = []): array
    {
        if ($reservation->status !== ReservationStatus::Approved) {
            throw ValidationException::withMessages([
                'status' => ['Only approved reservations can be checked in.'],
            ]);
        }

        if ($reservation->deposit_amount !== null && $reservation->deposit_paid_at === null) {
            throw ValidationException::withMessages([
                'deposit' => ['Mark the reservation deposit as paid before check-in.'],
            ]);
        }

        return DB::transaction(function () use ($reservation, $tenantId, $contractOverrides) {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            $reservation->load(['bedSpace', 'room', 'property']);

            $tenant = $tenantId !== null
                ? Tenant::query()
                    ->where('organization_id', $reservation->organization_id)
                    ->whereKey($tenantId)
                    ->firstOrFail()
                : Tenant::query()->create([
                    'organization_id' => $reservation->organization_id,
                    'first_name' => $reservation->applicant_first_name,
                    'last_name' => $reservation->applicant_last_name,
                    'email' => $reservation->applicant_email,
                    'phone' => $reservation->applicant_phone,
                    'status' => TenantStatus::Prospect,
                ]);

            $contract = $this->contractService->createFromReservation($reservation, $tenant, $contractOverrides);

            $this->assignmentService->assign(
                $tenant,
                $reservation->bedSpace,
                $reservation->requested_check_in->toDateString(),
            );

            $reservation->update([
                'status' => ReservationStatus::CheckedIn,
                'tenant_id' => $tenant->id,
                'checked_in_at' => now(),
            ]);

            return [
                'tenant' => $tenant->fresh(),
                'contract' => $contract,
                'reservation' => $reservation->fresh(),
            ];
        });
    }

    public function complete(Reservation $reservation): Reservation
    {
        if ($reservation->status !== ReservationStatus::CheckedIn) {
            throw ValidationException::withMessages(['status' => ['Reservation is not checked in.']]);
        }

        $reservation->update(['status' => ReservationStatus::Completed]);

        return $reservation->fresh();
    }
}
