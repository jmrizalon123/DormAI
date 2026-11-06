<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::OnHold => 'On hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'secondary',
            self::Assigned => 'info',
            self::InProgress => 'primary',
            self::OnHold => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'dark',
        };
    }
}
