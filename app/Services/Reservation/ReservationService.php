<?php

namespace App\Services\Reservation;

use App\Enums\BedSpaceStatus;
use App\Enums\ReservationStatus;
use App\Enums\TenantAssignmentStatus;
use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\BedSpace;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Notifications\ReservationSubmittedNotification;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitPublicRequest(Organization $organization, BedSpace $bedSpace, array $data): Reservation
    {
        $this->assertBedBelongsToOrganization($organization, $bedSpace);

        return DB::transaction(function () use ($organization, $bedSpace, $data) {
            $bedSpace = BedSpace::query()->lockForUpdate()->findOrFail($bedSpace->id);
            $this->assertBedBookable($bedSpace);

            $room = $bedSpace->room;

            $reservation = Reservation::query()->create([
                'organization_id' => $organization->id,
                'property_id' => $room->property_id,
                'room_id' => $room->id,
                'bed_space_id' => $bedSpace->id,
                'reference_code' => $this->generateReferenceCode($organization->id),
                'applicant_first_name' => $data['applicant_first_name'],
                'applicant_last_name' => $data['applicant_last_name'],
                'applicant_email' => $data['applicant_email'],
                'applicant_phone' => $data['applicant_phone'] ?? null,
                'requested_check_in' => $data['requested_check_in'],
                'requested_check_out' => $data['requested_check_out'] ?? null,
                'status' => ReservationStatus::Pending,
                'deposit_amount' => $data['deposit_amount'] ?? null,
                'applicant_notes' => $data['applicant_notes'] ?? null,
                'expires_at' => now()->addDays(7),
            ]);

            if (! empty($data['deposit_amount'])) {
                ReservationItem::query()->create([
                    'reservation_id' => $reservation->id,
                    'item_type' => 'deposit',
                    'description' => 'Reservation deposit',
                    'amount' => $data['deposit_amount'],
                ]);
            }

            $this->auditLogger->log('reservation.submitted', $reservation, [], $organization->id);

            $reservation->load(['property', 'room', 'bedSpace']);
            $this->notifyOrganizationStaff($organization->id, $reservation);

            return $reservation;
        });
    }

    private function notifyOrganizationStaff(int $organizationId, Reservation $reservation): void
    {
        $recipients = User::query()
            ->where('organization_id', $organizationId)
            ->whereIn('account_type', [UserAccountType::OrganizationOwner, UserAccountType::Staff])
            ->where('status', UserStatus::Active)
            ->get();

        foreach ($recipients as $user) {
            if ($user->can('manage reservations')) {
                $user->notify(new ReservationSubmittedNotification($reservation));
            }
        }
    }

    public function approve(Reservation $reservation, User $staff): Reservation
    {
        if ($reservation->status !== ReservationStatus::Pending) {
            throw ValidationException::withMessages(['status' => ['Only pending reservations can be approved.']]);
        }

        return DB::transaction(function () use ($reservation, $staff) {
            $bedSpace = BedSpace::query()->lockForUpdate()->findOrFail($reservation->bed_space_id);
            $this->assertBedBookable($bedSpace, $reservation->id);

            $bedSpace->update(['status' => BedSpaceStatus::Reserved]);

            $reservation->update([
                'status' => ReservationStatus::Approved,
                'approved_by' => $staff->id,
                'approved_at' => now(),
            ]);

            $this->auditLogger->log('reservation.approved', $reservation);

            return $reservation->fresh();
        });
    }

    public function reject(Reservation $reservation, User $staff, string $reason): Reservation
    {
        if (! in_array($reservation->status, [ReservationStatus::Pending, ReservationStatus::Approved], true)) {
            throw ValidationException::withMessages(['status' => ['This reservation cannot be rejected.']]);
        }

        return DB::transaction(function () use ($reservation, $staff, $reason) {
            if ($reservation->status === ReservationStatus::Approved) {
                $this->releaseBedIfReservedFor($reservation);
            }

            $reservation->update([
                'status' => ReservationStatus::Rejected,
                'rejection_reason' => $reason,
                'approved_by' => $staff->id,
                'approved_at' => now(),
            ]);

            $this->auditLogger->log('reservation.rejected', $reservation, ['reason' => $reason]);

            return $reservation->fresh();
        });
    }

    public function cancel(Reservation $reservation): Reservation
    {
        if (! in_array($reservation->status, [ReservationStatus::Pending, ReservationStatus::Approved], true)) {
            throw ValidationException::withMessages(['status' => ['This reservation cannot be cancelled.']]);
        }

        return DB::transaction(function () use ($reservation) {
            if ($reservation->status === ReservationStatus::Approved) {
                $this->releaseBedIfReservedFor($reservation);
            }

            $reservation->update([
                'status' => ReservationStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            $this->auditLogger->log('reservation.cancelled', $reservation);

            return $reservation->fresh();
        });
    }

    public function markDepositPaid(Reservation $reservation): Reservation
    {
        if ($reservation->deposit_amount === null) {
            throw ValidationException::withMessages(['deposit' => ['This reservation has no deposit amount.']]);
        }

        $reservation->update(['deposit_paid_at' => now()]);
        $this->auditLogger->log('reservation.deposit_paid', $reservation);

        return $reservation->fresh();
    }

    public function expireDueReservations(): int
    {
        $count = 0;

        Reservation::query()
            ->where('status', ReservationStatus::Pending)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->each(function (Reservation $reservation) use (&$count) {
                DB::transaction(function () use ($reservation) {
                    $reservation->update(['status' => ReservationStatus::Expired]);
                    $this->auditLogger->log('reservation.expired', $reservation);
                });
                $count++;
            });

        return $count;
    }

    private function assertBedBelongsToOrganization(Organization $organization, BedSpace $bedSpace): void
    {
        if ((int) $bedSpace->organization_id !== (int) $organization->id) {
            throw ValidationException::withMessages(['bed_space_id' => ['Invalid bed selection.']]);
        }
    }

    private function assertBedBookable(BedSpace $bedSpace, ?int $ignoreReservationId = null): void
    {
        if ($bedSpace->status === BedSpaceStatus::Occupied) {
            throw ValidationException::withMessages(['bed_space_id' => ['This bed is already occupied.']]);
        }

        if ($bedSpace->status === BedSpaceStatus::Maintenance || $bedSpace->status === BedSpaceStatus::Inactive) {
            throw ValidationException::withMessages(['bed_space_id' => ['This bed is not bookable.']]);
        }

        $activeAssignment = TenantAssignment::query()
            ->where('bed_space_id', $bedSpace->id)
            ->where('status', TenantAssignmentStatus::Active)
            ->exists();

        if ($activeAssignment) {
            throw ValidationException::withMessages(['bed_space_id' => ['This bed has an active tenant assignment.']]);
        }

        $conflicting = Reservation::query()
            ->where('bed_space_id', $bedSpace->id)
            ->whereIn('status', [ReservationStatus::Pending, ReservationStatus::Approved])
            ->when($ignoreReservationId, fn ($q) => $q->where('id', '!=', $ignoreReservationId))
            ->exists();

        if ($conflicting) {
            throw ValidationException::withMessages(['bed_space_id' => ['This bed already has a pending or approved reservation.']]);
        }
    }

    private function releaseBedIfReservedFor(Reservation $reservation): void
    {
        $bedSpace = BedSpace::query()->lockForUpdate()->find($reservation->bed_space_id);

        if ($bedSpace === null) {
            return;
        }

        if ($bedSpace->status === BedSpaceStatus::Reserved) {
            $bedSpace->update(['status' => BedSpaceStatus::Available]);
        }
    }

    private function generateReferenceCode(int $organizationId): string
    {
        do {
            $code = 'RES-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Reservation::query()->where('organization_id', $organizationId)->where('reference_code', $code)->exists());

        return $code;
    }
}
