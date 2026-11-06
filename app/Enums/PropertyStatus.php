<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Maintenance => 'Under maintenance',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'secondary',
            self::Maintenance => 'warning',
        };
    }
}
