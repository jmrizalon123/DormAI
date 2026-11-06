<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Prospect = 'prospect';
    case Active = 'active';
    case Inactive = 'inactive';
    case MovedOut = 'moved_out';

    public function label(): string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::MovedOut => 'Moved out',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Prospect => 'info',
            self::Active => 'success',
            self::Inactive => 'secondary',
            self::MovedOut => 'warning',
        };
    }
}
