<?php

namespace App\Enums;

enum TenantAssignmentStatus: string
{
    case Active = 'active';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Ended => 'Ended',
        };
    }
}
