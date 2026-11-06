<?php

namespace App\Support\Notifications;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Models\WorkOrder;

class NotificationRoutes
{
    public static function invoice(Invoice $invoice, User $user): string
    {
        if ($user->isTenant()) {
            return route('portal.invoices.show', $invoice);
        }

        return route('organization.invoices.show', $invoice);
    }

    public static function contract(Contract $contract, User $user): string
    {
        if ($user->isTenant()) {
            return route('portal.contracts.show', $contract);
        }

        return route('organization.contracts.show', $contract);
    }

    public static function maintenanceRequest(MaintenanceRequest $request, User $user): string
    {
        if ($user->isTenant()) {
            return route('portal.maintenance.show', $request);
        }

        return route('organization.maintenance-requests.show', $request);
    }

    public static function workOrder(WorkOrder $workOrder, User $user): string
    {
        return route('organization.work-orders.show', $workOrder);
    }

    public static function reservation(Reservation $reservation, User $user): string
    {
        return route('organization.reservations.show', $reservation);
    }
}
