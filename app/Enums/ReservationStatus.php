<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
            self::CheckedIn => 'Checked in',
            self::Completed => 'Completed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::Cancelled => 'secondary',
            self::Expired => 'dark',
            self::CheckedIn => 'success',
            self::Completed => 'primary',
        };
    }
}
