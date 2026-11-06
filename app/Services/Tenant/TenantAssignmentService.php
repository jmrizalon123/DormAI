<?php

namespace App\Services\Tenant;

use App\Enums\BedSpaceStatus;
use App\Enums\TenantAssignmentStatus;
use App\Enums\TenantStatus;
use App\Models\BedSpace;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Models\TenantAssignmentHistory;
use App\Services\Audit\AuditLogger;
use App\Services\Property\OccupancyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantAssignmentService
{
    public function __construct(
        private readonly OccupancyService $occupancyService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function assign(Tenant $tenant, BedSpace $bedSpace, string $moveInDate): TenantAssignment
    {
        if ($tenant->activeAssignment()->exists()) {
            throw ValidationException::withMessages([
                'bed_space_id' => ['This tenant already has an active bed assignment. End the current assignment first.'],
            ]);
        }

        if (! in_array($bedSpace->status, [BedSpaceStatus::Available, BedSpaceStatus::Reserved], true)) {
            throw ValidationException::withMessages([
                'bed_space_id' => ['The selected bed is not available for assignment.'],
            ]);
        }

        $hasActiveBedAssignment = TenantAssignment::query()
            ->where('bed_space_id', $bedSpace->id)
            ->where('status', TenantAssignmentStatus::Active)
            ->exists();

        if ($hasActiveBedAssignment) {
            throw ValidationException::withMessages([
                'bed_space_id' => ['This bed already has an active tenant assignment.'],
            ]);
        }

        $room = $bedSpace->room()->with(['building', 'floor'])->firstOrFail();

        return DB::transaction(function () use ($tenant, $bedSpace, $room, $moveInDate) {
            $assignment = TenantAssignment::query()->create([
                'organization_id' => $tenant->organization_id,
                'tenant_id' => $tenant->id,
                'property_id' => $room->property_id,
                'building_id' => $room->building_id,
                'floor_id' => $room->floor_id,
                'room_id' => $room->id,
                'bed_space_id' => $bedSpace->id,
                'move_in_date' => $moveInDate,
                'status' => TenantAssignmentStatus::Active,
            ]);

            $bedSpace->update(['status' => BedSpaceStatus::Occupied]);
            $this->occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));

            if ($tenant->status === TenantStatus::Prospect) {
                $tenant->update(['status' => TenantStatus::Active]);
            }

            $this->recordHistory($tenant, $assignment, 'assigned', [
                'move_in_date' => $moveInDate,
                'bed_label' => $bedSpace->label,
                'room_number' => $room->room_number,
            ]);

            $this->auditLogger->log('tenant.assigned', $tenant, [
                'assignment_id' => $assignment->id,
                'bed_space_id' => $bedSpace->id,
            ]);

            return $assignment;
        });
    }

    public function moveOut(TenantAssignment $assignment, ?string $moveOutDate = null, ?string $reason = null): TenantAssignment
    {
        if ($assignment->status !== TenantAssignmentStatus::Active) {
            throw ValidationException::withMessages([
                'assignment' => ['This assignment is already ended.'],
            ]);
        }

        return DB::transaction(function () use ($assignment, $moveOutDate, $reason) {
            $moveOut = $moveOutDate ?? now()->toDateString();

            $assignment->update([
                'status' => TenantAssignmentStatus::Ended,
                'move_out_date' => $moveOut,
                'ended_reason' => $reason,
            ]);

            $bedSpace = $assignment->bedSpace;
            $room = $assignment->room;

            $bedSpace->update(['status' => BedSpaceStatus::Available]);
            $this->occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));

            $tenant = $assignment->tenant;

            if (! $tenant->assignments()->where('status', TenantAssignmentStatus::Active)->exists()) {
                $tenant->update(['status' => TenantStatus::MovedOut]);
            }

            $this->recordHistory($tenant, $assignment, 'moved_out', [
                'move_out_date' => $moveOut,
                'reason' => $reason,
            ]);

            $this->auditLogger->log('tenant.moved_out', $tenant, [
                'assignment_id' => $assignment->id,
            ]);

            return $assignment->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordHistory(
        Tenant $tenant,
        TenantAssignment $assignment,
        string $event,
        array $meta = [],
    ): void {
        TenantAssignmentHistory::query()->create([
            'organization_id' => $tenant->organization_id,
            'tenant_id' => $tenant->id,
            'tenant_assignment_id' => $assignment->id,
            'event' => $event,
            'property_id' => $assignment->property_id,
            'room_id' => $assignment->room_id,
            'bed_space_id' => $assignment->bed_space_id,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
