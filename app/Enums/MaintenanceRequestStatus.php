<?php

namespace App\Enums;

enum MaintenanceRequestStatus: string
{
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case WorkOrderCreated = 'work_order_created';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::InReview => 'In review',
            self::WorkOrderCreated => 'Work order created',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::InReview => 'primary',
            self::WorkOrderCreated => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'dark',
        };
    }
}
