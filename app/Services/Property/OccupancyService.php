<?php

namespace App\Services\Property;

use App\Enums\BedSpaceStatus;
use App\Enums\RoomStatus;
use App\Models\BedSpace;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Support\Collection;

class OccupancyService
{
    /**
     * @return array{total: int, occupied: int, available: int, reserved: int, maintenance: int, rate: float|null}
     */
    public function summarizeBedSpaces(Collection $bedSpaces): array
    {
        $total = $bedSpaces->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'occupied' => 0,
                'available' => 0,
                'reserved' => 0,
                'maintenance' => 0,
                'rate' => null,
            ];
        }

        $occupied = $bedSpaces->where('status', BedSpaceStatus::Occupied)->count();
        $available = $bedSpaces->where('status', BedSpaceStatus::Available)->count();
        $reserved = $bedSpaces->where('status', BedSpaceStatus::Reserved)->count();
        $maintenance = $bedSpaces->whereIn('status', [BedSpaceStatus::Maintenance, BedSpaceStatus::Inactive])->count();

        $activeTotal = $total - $maintenance;
        $rate = $activeTotal > 0 ? round(($occupied / $activeTotal) * 100, 1) : null;

        return compact('total', 'occupied', 'available', 'reserved', 'maintenance', 'rate');
    }

    public function syncRoomStatus(Room $room): Room
    {
        $room->loadMissing('bedSpaces');

        if ($room->status === RoomStatus::Inactive || $room->status === RoomStatus::Maintenance) {
            return $room;
        }

        $summary = $this->summarizeBedSpaces($room->bedSpaces);

        if ($summary['total'] === 0) {
            $room->update(['status' => RoomStatus::Available]);

            return $room->fresh();
        }

        if ($summary['occupied'] >= $summary['total'] - $summary['maintenance']) {
            $status = $summary['available'] === 0 && $summary['reserved'] === 0
                ? RoomStatus::Full
                : RoomStatus::Partial;
        } elseif ($summary['occupied'] > 0 || $summary['reserved'] > 0) {
            $status = RoomStatus::Partial;
        } else {
            $status = RoomStatus::Available;
        }

        $room->update(['status' => $status]);

        return $room->fresh();
    }

    /**
     * @return array{rooms: int, bed_spaces: int, occupied: int, available: int, occupancy_rate: float|null}
     */
    public function propertySummary(Property $property): array
    {
        $bedSpaces = BedSpace::query()
            ->where('organization_id', $property->organization_id)
            ->whereHas('room', fn ($query) => $query->where('property_id', $property->id))
            ->get();

        $summary = $this->summarizeBedSpaces($bedSpaces);
        $rooms = Room::query()->where('property_id', $property->id)->count();

        return [
            'rooms' => $rooms,
            'bed_spaces' => $summary['total'],
            'occupied' => $summary['occupied'],
            'available' => $summary['available'],
            'occupancy_rate' => $summary['rate'],
        ];
    }
}
