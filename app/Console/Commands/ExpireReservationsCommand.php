<?php

namespace App\Console\Commands;

use App\Services\Reservation\ReservationService;
use Illuminate\Console\Command;

class ExpireReservationsCommand extends Command
{
    protected $signature = 'dormai:expire-reservations';

    protected $description = 'Mark pending reservations past their expiration as expired';

    public function handle(ReservationService $reservationService): int
    {
        $count = $reservationService->expireDueReservations();
        $this->info("Expired {$count} reservation(s).");

        return self::SUCCESS;
    }
}
