<?php

namespace App\Http\Controllers\Organization;

use App\Enums\BedSpaceStatus;
use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Models\Room;
use App\Services\Audit\AuditLogger;
use App\Services\Property\OccupancyService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BedSpaceController extends Controller
{
    public function store(
        Request $request,
        Room $room,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
        OccupancyService $occupancyService,
    ): RedirectResponse {
        $this->authorize('create', BedSpace::class);
        $this->authorize('update', $room);

        if ($room->bedSpaces()->count() >= $room->capacity) {
            return back()->withErrors(['label' => 'This room has reached its bed capacity. Increase room capacity first.']);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:32'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_column(BedSpaceStatus::cases(), 'value'))],
        ]);

        DB::transaction(function () use ($room, $validated, $organizationContext, $auditLogger, $occupancyService) {
            $bedSpace = $room->bedSpaces()->create([
                'organization_id' => $organizationContext->ensureId(),
                'label' => strtoupper($validated['label']),
                'monthly_rate' => $validated['monthly_rate'] ?? $room->monthly_rate,
                'status' => $validated['status'],
            ]);

            $occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));
            $auditLogger->log('bed_space.created', $bedSpace);
        });

        return back()->with('success', 'Bed space added.');
    }

    public function update(
        Request $request,
        Room $room,
        BedSpace $bedSpace,
        AuditLogger $auditLogger,
        OccupancyService $occupancyService,
    ): RedirectResponse {
        $this->authorize('update', $bedSpace);
        $this->authorize('update', $room);

        if ((int) $bedSpace->room_id !== (int) $room->id) {
            abort(404);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:32'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_column(BedSpaceStatus::cases(), 'value'))],
        ]);

        DB::transaction(function () use ($bedSpace, $validated, $room, $auditLogger, $occupancyService) {
            $bedSpace->update([
                'label' => strtoupper($validated['label']),
                'monthly_rate' => $validated['monthly_rate'],
                'status' => $validated['status'],
            ]);

            $occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));
            $auditLogger->log('bed_space.updated', $bedSpace);
        });

        return back()->with('success', 'Bed space updated.');
    }

    public function destroy(
        Room $room,
        BedSpace $bedSpace,
        AuditLogger $auditLogger,
        OccupancyService $occupancyService,
    ): RedirectResponse {
        $this->authorize('delete', $bedSpace);
        $this->authorize('update', $room);

        if ((int) $bedSpace->room_id !== (int) $room->id) {
            abort(404);
        }

        if ($bedSpace->status === BedSpaceStatus::Occupied) {
            return back()->withErrors(['bed_space' => 'Occupied bed spaces cannot be removed.']);
        }

        DB::transaction(function () use ($bedSpace, $room, $auditLogger, $occupancyService) {
            $auditLogger->log('bed_space.deleted', $bedSpace);
            $bedSpace->delete();
            $occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));
        });

        return back()->with('success', 'Bed space removed.');
    }
}
