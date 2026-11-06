<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Partial = 'partial';
    case Full = 'full';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Partial => 'Partially occupied',
            self::Full => 'Full',
            self::Maintenance => 'Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Partial => 'info',
            self::Full => 'primary',
            self::Maintenance => 'warning',
            self::Inactive => 'secondary',
        };
    }
}
