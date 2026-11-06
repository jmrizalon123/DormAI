<?php

namespace App\Enums;

enum BedSpaceStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Occupied => 'Occupied',
            self::Reserved => 'Reserved',
            self::Maintenance => 'Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Occupied => 'primary',
            self::Reserved => 'info',
            self::Maintenance => 'warning',
            self::Inactive => 'secondary',
        };
    }
}
