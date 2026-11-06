<?php

namespace App\Services\Reports;

use App\Enums\ReservationStatus;
use App\Models\Organization;
use App\Models\Reservation;
use Carbon\CarbonInterface;

class ReservationReportService
{
    /**
     * @return array{
     *     total: int,
     *     by_status: array<string, int>,
     *     conversion_rate: float|null
     * }
     */
    public function build(Organization $organization, CarbonInterface $from, CarbonInterface $to): array
    {
        $organizationId = $organization->id;

        $base = Reservation::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $total = (clone $base)->count();

        $byStatus = [];
        foreach (ReservationStatus::cases() as $status) {
            $byStatus[$status->value] = (clone $base)->where('status', $status)->count();
        }

        $approvedOrBeyond = $byStatus[ReservationStatus::Approved->value]
            + $byStatus[ReservationStatus::CheckedIn->value]
            + $byStatus[ReservationStatus::Completed->value];

        $checkedIn = $byStatus[ReservationStatus::CheckedIn->value]
            + $byStatus[ReservationStatus::Completed->value];

        $conversionRate = $approvedOrBeyond > 0
            ? round(($checkedIn / $approvedOrBeyond) * 100, 1)
            : null;

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'conversion_rate' => $conversionRate,
        ];
    }
}
