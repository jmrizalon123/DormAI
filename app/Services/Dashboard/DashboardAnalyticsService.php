<?php

namespace App\Services\Dashboard;

use App\Models\Organization;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\TenantAssignment;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class DashboardAnalyticsService
{
    private const MONTHS = 6;

    /**
     * @param  array{
     *     properties_total: int,
     *     rooms_total: int,
     *     bed_spaces_total: int,
     *     bed_spaces_available: int,
     *     occupancy_rate: float|null,
     *     active_tenants: int,
     *     pending_reservations: int
     * }  $stats
     * @param  array{total: int, occupied: int, available: int, reserved: int, maintenance: int, rate: float|null}  $bedSummary
     * @return array{
     *     cards: list<array<string, mixed>>,
     *     featured: list<array<string, mixed>>
     * }
     */
    public function build(Organization $organization, array $stats, array $bedSummary): array
    {
        $months = $this->monthLabels();
        $organizationId = $organization->id;
        $totalBeds = max(1, $stats['bed_spaces_total'] - $bedSummary['maintenance']);

        $propertySeries = $this->countCreatedPerMonth(Property::query()->where('organization_id', $organizationId));
        $roomSeries = $this->countCreatedPerMonth(Room::query()->where('organization_id', $organizationId));
        $occupancySeries = $months->map(fn (Carbon $month) => round(
            ($this->activeAssignmentsAt($organizationId, $month->copy()->endOfMonth()) / $totalBeds) * 100,
            1,
        ))->all();

        $activeTenantSeries = $months->map(
            fn (Carbon $month) => $this->activeAssignmentsAt($organizationId, $month->copy()->endOfMonth()),
        )->all();

        $reservationSeries = $months->map(function (Carbon $month) use ($organizationId) {
            return Reservation::query()
                ->where('organization_id', $organizationId)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        })->all();

        $moveInSeries = $months->map(function (Carbon $month) use ($organizationId) {
            return TenantAssignment::query()
                ->where('organization_id', $organizationId)
                ->whereBetween('move_in_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->count();
        })->all();

        $moveOutSeries = $months->map(function (Carbon $month) use ($organizationId) {
            return TenantAssignment::query()
                ->where('organization_id', $organizationId)
                ->whereBetween('move_out_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->count();
        })->all();

        $availableSeries = $months->map(function (Carbon $month) use ($organizationId, $stats, $bedSummary) {
            $occupied = $this->activeAssignmentsAt($organizationId, $month->copy()->endOfMonth());

            return max(0, $stats['bed_spaces_total'] - $bedSummary['maintenance'] - $bedSummary['reserved'] - $occupied);
        })->all();

        $cards = [
            $this->card(
                'dash-properties',
                'Properties',
                (string) $stats['properties_total'],
                'bi-buildings',
                '#2563eb',
                'line',
                $months,
                $propertySeries,
                'New this month',
            ),
            $this->card(
                'dash-rooms',
                'Rooms',
                (string) $stats['rooms_total'],
                'bi-door-open',
                '#7c3aed',
                'line',
                $months,
                $roomSeries,
                'New this month',
            ),
            $this->card(
                'dash-beds',
                'Bed spaces',
                (string) $stats['bed_spaces_total'],
                'bi-grid-3x3-gap',
                '#0891b2',
                'bar',
                ['Occ', 'Avail', 'Res', 'Maint'],
                [
                    $bedSummary['occupied'],
                    $bedSummary['available'],
                    $bedSummary['reserved'],
                    $bedSummary['maintenance'],
                ],
                'Status mix',
                false,
            ),
            $this->card(
                'dash-available',
                'Available beds',
                (string) $stats['bed_spaces_available'],
                'bi-check2-circle',
                '#16a34a',
                'line',
                $months,
                $availableSeries,
                '6-month trend',
            ),
            $this->card(
                'dash-occupancy',
                'Occupancy rate',
                $stats['occupancy_rate'] !== null ? $stats['occupancy_rate'].'%' : '—',
                'bi-pie-chart',
                '#ea580c',
                'line',
                $months,
                $occupancySeries,
                '6-month trend',
            ),
            $this->card(
                'dash-tenants',
                'Active tenants',
                (string) $stats['active_tenants'],
                'bi-people',
                '#4f46e5',
                'line',
                $months,
                $activeTenantSeries,
                '6-month trend',
            ),
        ];

        if (Gate::allows('viewAny', Reservation::class)) {
            $cards[] = $this->card(
                'dash-reservations',
                'Pending reservations',
                (string) $stats['pending_reservations'],
                'bi-calendar2-check',
                '#db2777',
                'bar',
                $months->map->format('M')->all(),
                $reservationSeries,
                'Requests / month',
            );
        }

        $featured = [
            [
                'id' => 'dash-featured-occupancy',
                'title' => 'Occupancy rate',
                'subtitle' => 'Trailing six months',
                'type' => 'line',
                'labels' => $months->map->format('M Y')->all(),
                'datasets' => [[
                    'label' => 'Occupancy %',
                    'data' => $occupancySeries,
                    'borderColor' => '#ea580c',
                    'backgroundColor' => 'rgba(234, 88, 12, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
            [
                'id' => 'dash-featured-movement',
                'title' => 'Tenant movement',
                'subtitle' => 'Move-ins vs move-outs',
                'type' => 'bar',
                'labels' => $months->map->format('M Y')->all(),
                'datasets' => [
                    [
                        'label' => 'Move-ins',
                        'data' => $moveInSeries,
                        'backgroundColor' => 'rgba(22, 163, 74, 0.75)',
                        'borderRadius' => 6,
                    ],
                    [
                        'label' => 'Move-outs',
                        'data' => $moveOutSeries,
                        'backgroundColor' => 'rgba(107, 114, 128, 0.55)',
                        'borderRadius' => 6,
                    ],
                ],
            ],
        ];

        if (Gate::allows('viewFinancialReports')) {
            $revenueSeries = $months->map(function (Carbon $month) use ($organizationId) {
                return (float) Payment::query()
                    ->where('organization_id', $organizationId)
                    ->whereBetween('paid_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('amount');
            })->all();

            $featured[] = [
                'id' => 'dash-featured-revenue',
                'title' => 'Collections',
                'subtitle' => 'Payments received',
                'type' => 'line',
                'labels' => $months->map->format('M Y')->all(),
                'datasets' => [[
                    'label' => 'Collected (₱)',
                    'data' => $revenueSeries,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ];
        }

        return compact('cards', 'featured');
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function monthLabels(): Collection
    {
        return collect(range(self::MONTHS - 1, 0))
            ->map(fn (int $ago) => now()->copy()->subMonths($ago)->startOfMonth());
    }

    /**
     * @param  Builder<Model>  $query
     * @return list<int>
     */
    private function countCreatedPerMonth($query): array
    {
        return $this->monthLabels()->map(function (Carbon $month) use ($query) {
            return (clone $query)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        })->all();
    }

    private function activeAssignmentsAt(int $organizationId, CarbonInterface $onDate): int
    {
        $date = $onDate->toDateString();

        return TenantAssignment::query()
            ->where('organization_id', $organizationId)
            ->where('move_in_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('move_out_date')
                    ->orWhere('move_out_date', '>', $date);
            })
            ->count();
    }

    /**
     * @param  array<int, string>|Collection<int, Carbon|string>  $labels
     * @param  list<int|float>  $series
     * @return array<string, mixed>
     */
    private function card(
        string $id,
        string $label,
        string $value,
        string $icon,
        string $color,
        string $type,
        array|Collection $labels,
        array $series,
        string $caption,
        bool $computeTrend = true,
    ): array {
        $labelList = $labels instanceof Collection
            ? ($type === 'line' ? $labels->map->format('M')->all() : $labels->all())
            : array_values($labels);

        $trend = $computeTrend ? $this->trendFromSeries($series) : null;

        return [
            'id' => $id,
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'accent' => $color,
            'caption' => $caption,
            'trend' => $trend,
            'chart' => [
                'type' => $type,
                'labels' => $labelList,
                'data' => array_values($series),
                'color' => $color,
            ],
        ];
    }

    /**
     * @param  list<int|float>  $series
     * @return array{direction: string, text: string}|null
     */
    private function trendFromSeries(array $series): ?array
    {
        if (count($series) < 2) {
            return null;
        }

        $last = (float) $series[array_key_last($series)];
        $prev = (float) $series[count($series) - 2];

        if ($last === $prev) {
            return ['direction' => 'flat', 'text' => 'Flat vs prior month'];
        }

        if ($prev == 0.0) {
            return [
                'direction' => $last > 0 ? 'up' : 'down',
                'text' => $last > 0 ? 'Up from zero' : 'Down',
            ];
        }

        $pct = round((($last - $prev) / abs($prev)) * 100, 1);
        $direction = $pct > 0 ? 'up' : 'down';

        return [
            'direction' => $direction,
            'text' => ($pct > 0 ? '+' : '').$pct.'% vs prior month',
        ];
    }
}
