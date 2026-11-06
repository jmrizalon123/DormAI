<?php

namespace App\Http\Controllers\Organization;

use App\Enums\BedSpaceStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomAmenity;
use App\Models\RoomType;
use App\Services\Audit\AuditLogger;
use App\Services\Property\OccupancyService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function create(Floor $floor, OrganizationContext $organizationContext): View
    {
        $this->authorize('create', Room::class);
        $this->authorize('update', $floor);

        $floor->load('building.property');

        return view('organization.rooms.create', [
            'floor' => $floor,
            'roomTypes' => RoomType::query()->where('organization_id', $organizationContext->ensureId())->orderBy('name')->get(),
            'amenities' => RoomAmenity::query()->where('organization_id', $organizationContext->ensureId())->orderBy('name')->get(),
        ]);
    }

    public function store(
        Request $request,
        Floor $floor,
        OrganizationContext $organizationContext,
        AuditLogger $auditLogger,
        OccupancyService $occupancyService,
    ): RedirectResponse {
        $this->authorize('create', Room::class);
        $this->authorize('update', $floor);

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:32'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'monthly_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:room_amenities,id'],
        ]);

        $room = DB::transaction(function () use ($validated, $floor, $organizationContext, $auditLogger, $occupancyService) {
            $building = $floor->building;

            $room = Room::query()->create([
                'organization_id' => $organizationContext->ensureId(),
                'property_id' => $building->property_id,
                'building_id' => $building->id,
                'floor_id' => $floor->id,
                'room_number' => $validated['room_number'],
                'room_type_id' => $validated['room_type_id'] ?? null,
                'capacity' => $validated['capacity'],
                'monthly_rate' => $validated['monthly_rate'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! empty($validated['amenity_ids'])) {
                $room->amenities()->sync($validated['amenity_ids']);
            }

            for ($index = 0; $index < (int) $validated['capacity']; $index++) {
                $room->bedSpaces()->create([
                    'organization_id' => $organizationContext->ensureId(),
                    'label' => self::bedLabel($index),
                    'monthly_rate' => $validated['monthly_rate'],
                    'status' => BedSpaceStatus::Available,
                ]);
            }

            $occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));
            $auditLogger->log('room.created', $room);

            return $room;
        });

        return redirect()
            ->route('organization.rooms.show', $room)
            ->with('success', 'Room created with bed spaces.');
    }

    public function show(Room $room, OccupancyService $occupancyService): View
    {
        $this->authorize('view', $room);

        $room->load(['property', 'building', 'floor', 'roomType', 'bedSpaces', 'amenities', 'images']);
        $bedSummary = $occupancyService->summarizeBedSpaces($room->bedSpaces);

        return view('organization.rooms.show', compact('room', 'bedSummary'));
    }

    public function edit(Room $room, OrganizationContext $organizationContext): View
    {
        $this->authorize('update', $room);

        $room->load(['floor.building.property', 'amenities', 'bedSpaces']);

        return view('organization.rooms.edit', [
            'room' => $room,
            'roomTypes' => RoomType::query()->where('organization_id', $organizationContext->ensureId())->orderBy('name')->get(),
            'amenities' => RoomAmenity::query()->where('organization_id', $organizationContext->ensureId())->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Room $room, AuditLogger $auditLogger, OccupancyService $occupancyService): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:32'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'monthly_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_column(RoomStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:room_amenities,id'],
        ]);

        if ((int) $validated['capacity'] < $room->bedSpaces()->count()) {
            return back()->withErrors([
                'capacity' => 'Capacity cannot be less than the number of existing bed spaces.',
            ])->withInput();
        }

        DB::transaction(function () use ($room, $validated, $auditLogger, $occupancyService) {
            $room->update([
                'room_number' => $validated['room_number'],
                'room_type_id' => $validated['room_type_id'] ?? null,
                'capacity' => $validated['capacity'],
                'monthly_rate' => $validated['monthly_rate'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $room->amenities()->sync($validated['amenity_ids'] ?? []);

            $existingCount = $room->bedSpaces()->count();
            $target = (int) $validated['capacity'];

            if ($target > $existingCount) {
                for ($index = $existingCount; $index < $target; $index++) {
                    $room->bedSpaces()->create([
                        'organization_id' => $room->organization_id,
                        'label' => self::bedLabel($index),
                        'monthly_rate' => $validated['monthly_rate'],
                        'status' => BedSpaceStatus::Available,
                    ]);
                }
            }

            $occupancyService->syncRoomStatus($room->fresh(['bedSpaces']));
            $auditLogger->log('room.updated', $room);
        });

        return redirect()
            ->route('organization.rooms.show', $room)
            ->with('success', 'Room updated.');
    }

    public function destroy(Room $room, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $room);

        $floor = $room->floor;
        $auditLogger->log('room.deleted', $room);
        $room->delete();

        return redirect()
            ->route('organization.floors.show', $floor)
            ->with('success', 'Room removed.');
    }

    private static function bedLabel(int $index): string
    {
        if ($index < 26) {
            return chr(65 + $index);
        }

        return 'B'.($index + 1);
    }
}
