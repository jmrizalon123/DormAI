<?php

namespace App\Services\Utilities;

use App\Enums\TenantAssignmentStatus;
use App\Enums\UtilityAllocationStatus;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantAssignment;
use App\Models\UtilityAllocation;
use App\Models\UtilityRate;
use App\Models\UtilityReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UtilityAllocationService
{
    /**
     * @return Collection<int, UtilityAllocation>
     */
    public function calculate(UtilityReading $reading): Collection
    {
        $reading->loadMissing('meter.property', 'meter.room');
        $meter = $reading->meter;

        if ($reading->consumption <= 0) {
            throw new InvalidArgumentException('Consumption must be greater than zero to allocate.');
        }

        $rate = UtilityRate::resolveFor($meter->property, $meter->utility_type, $reading->reading_date);
        if ($rate === null) {
            throw new InvalidArgumentException('No active utility rate found for this property and utility type.');
        }

        if ($reading->allocations()->where('status', UtilityAllocationStatus::Invoiced)->exists()) {
            throw new InvalidArgumentException('Invoiced allocations cannot be recalculated.');
        }

        return DB::transaction(function () use ($reading, $meter, $rate) {
            $reading->allocations()->delete();

            if ($meter->is_shared || $meter->room_id === null) {
                return $this->allocateShared($reading, $rate);
            }

            return collect([$this->allocateIndividualRoom($reading, $rate, $meter->room_id)]);
        });
    }

    /**
     * @return Collection<int, UtilityAllocation>
     */
    private function allocateShared(UtilityReading $reading, UtilityRate $rate): Collection
    {
        /** @var Property $property */
        $property = $reading->meter->property;
        $rooms = $this->roomsWithActiveAssignments($property->id, $reading->reading_date);

        if ($rooms->isEmpty()) {
            throw new InvalidArgumentException('No occupied rooms found for shared utility allocation.');
        }

        $consumptionPerRoom = round((float) $reading->consumption / $rooms->count(), 3);
        $amountPerRoom = round($consumptionPerRoom * (float) $rate->rate_per_unit, 2);

        return $rooms->map(function (Room $room) use ($reading, $rate, $consumptionPerRoom, $amountPerRoom) {
            $tenantId = $this->primaryTenantForRoom($room->id, $reading->reading_date);

            return UtilityAllocation::query()->create([
                'organization_id' => $reading->organization_id,
                'utility_reading_id' => $reading->id,
                'room_id' => $room->id,
                'tenant_id' => $tenantId,
                'consumption_units' => $consumptionPerRoom,
                'rate_per_unit' => $rate->rate_per_unit,
                'amount' => $amountPerRoom,
                'status' => UtilityAllocationStatus::Calculated,
            ]);
        });
    }

    private function allocateIndividualRoom(UtilityReading $reading, UtilityRate $rate, int $roomId): UtilityAllocation
    {
        $tenantId = $this->primaryTenantForRoom($roomId, $reading->reading_date);
        $amount = round((float) $reading->consumption * (float) $rate->rate_per_unit, 2);

        return UtilityAllocation::query()->create([
            'organization_id' => $reading->organization_id,
            'utility_reading_id' => $reading->id,
            'room_id' => $roomId,
            'tenant_id' => $tenantId,
            'consumption_units' => $reading->consumption,
            'rate_per_unit' => $rate->rate_per_unit,
            'amount' => $amount,
            'status' => UtilityAllocationStatus::Calculated,
        ]);
    }

    /**
     * @return Collection<int, Room>
     */
    private function roomsWithActiveAssignments(int $propertyId, string $onDate): Collection
    {
        $roomIds = TenantAssignment::query()
            ->where('property_id', $propertyId)
            ->where('status', TenantAssignmentStatus::Active)
            ->whereDate('move_in_date', '<=', $onDate)
            ->where(function ($q) use ($onDate) {
                $q->whereNull('move_out_date')->orWhereDate('move_out_date', '>=', $onDate);
            })
            ->pluck('room_id')
            ->unique()
            ->values();

        return Room::query()->whereIn('id', $roomIds)->get();
    }

    private function primaryTenantForRoom(int $roomId, string $onDate): ?int
    {
        return TenantAssignment::query()
            ->where('room_id', $roomId)
            ->where('status', TenantAssignmentStatus::Active)
            ->whereDate('move_in_date', '<=', $onDate)
            ->where(function ($q) use ($onDate) {
                $q->whereNull('move_out_date')->orWhereDate('move_out_date', '>=', $onDate);
            })
            ->orderBy('move_in_date')
            ->value('tenant_id');
    }
}
