<?php

namespace App\Services\Utilities;

use App\Enums\UtilityType;
use App\Models\Organization;
use App\Models\UtilityReading;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class UtilityReportService
{
    /**
     * @return array{
     *     total_consumption: array<string, float>,
     *     total_billed: float,
     *     readings_count: int,
     *     by_property: Collection
     * }
     */
    public function consumptionSummary(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $readings = UtilityReading::query()
            ->where('organization_id', $organization->id)
            ->whereBetween('reading_date', [$from->toDateString(), $to->toDateString()])
            ->with(['meter.property', 'allocations'])
            ->orderBy('reading_date')
            ->get();

        $totalConsumption = [];
        foreach (UtilityType::cases() as $type) {
            $totalConsumption[$type->value] = (float) $readings
                ->filter(fn (UtilityReading $r) => $r->meter->utility_type === $type)
                ->sum('consumption');
        }

        $totalBilled = (float) $readings->flatMap->allocations->sum('amount');

        $byProperty = $readings->groupBy(fn (UtilityReading $r) => $r->meter->property_id)
            ->map(function (Collection $group, $propertyId) {
                $property = $group->first()->meter->property;

                return [
                    'property' => $property->name,
                    'readings' => $group->count(),
                    'consumption' => (float) $group->sum('consumption'),
                    'billed' => (float) $group->flatMap->allocations->sum('amount'),
                ];
            })
            ->values();

        return [
            'total_consumption' => $totalConsumption,
            'total_billed' => $totalBilled,
            'readings_count' => $readings->count(),
            'by_property' => $byProperty,
        ];
    }
}
