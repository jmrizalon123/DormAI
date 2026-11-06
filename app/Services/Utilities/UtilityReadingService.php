<?php

namespace App\Services\Utilities;

use App\Enums\UtilityAllocationStatus;
use App\Models\User;
use App\Models\UtilityMeter;
use App\Models\UtilityReading;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UtilityReadingService
{
    public function record(
        UtilityMeter $meter,
        User $recorder,
        string $readingDate,
        float $currentReading,
        ?string $notes = null,
    ): UtilityReading {
        if ($currentReading < 0) {
            throw new InvalidArgumentException('Reading cannot be negative.');
        }

        $previous = (float) ($meter->latestReading()?->current_reading ?? 0);

        if ($currentReading < $previous) {
            throw new InvalidArgumentException('Current reading cannot be less than the previous reading.');
        }

        $consumption = round($currentReading - $previous, 3);

        return UtilityReading::query()->create([
            'organization_id' => $meter->organization_id,
            'utility_meter_id' => $meter->id,
            'recorded_by' => $recorder->id,
            'reading_date' => $readingDate,
            'previous_reading' => $previous,
            'current_reading' => $currentReading,
            'consumption' => $consumption,
            'notes' => $notes,
        ]);
    }

    public function delete(UtilityReading $reading): void
    {
        DB::transaction(function () use ($reading) {
            if ($reading->allocations()->where('status', UtilityAllocationStatus::Invoiced)->exists()) {
                throw new InvalidArgumentException('Cannot delete a reading with invoiced allocations.');
            }

            $reading->allocations()->delete();
            $reading->delete();
        });
    }
}
