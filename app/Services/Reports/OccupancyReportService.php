<?php

namespace App\Services\Reports;

use App\Models\BedSpace;
use App\Models\Organization;
use App\Models\Property;
use App\Services\Property\OccupancyService;
use Illuminate\Support\Collection;

class OccupancyReportService
{
    public function __construct(private OccupancyService $occupancyService) {}

    /**
     * @return array{
     *     summary: array{total: int, occupied: int, available: int, reserved: int, maintenance: int, rate: float|null},
     *     by_property: Collection<int, array{property: string, rooms: int, bed_spaces: int, occupied: int, available: int, occupancy_rate: float|null}>
     * }
     */
    public function build(Organization $organization): array
    {
        $bedSpaces = BedSpace::query()
            ->where('organization_id', $organization->id)
            ->get();
        $summary = $this->occupancyService->summarizeBedSpaces($bedSpaces);

        $byProperty = Property::query()
            ->where('organization_id', $organization->id)
            ->orderBy('name')
            ->get()
            ->map(function (Property $property) {
                $row = $this->occupancyService->propertySummary($property);

                return [
                    'property' => $property->name,
                    'rooms' => $row['rooms'],
                    'bed_spaces' => $row['bed_spaces'],
                    'occupied' => $row['occupied'],
                    'available' => $row['available'],
                    'occupancy_rate' => $row['occupancy_rate'],
                ];
            });

        return [
            'summary' => $summary,
            'by_property' => $byProperty,
        ];
    }
}
