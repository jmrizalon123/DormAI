<?php

namespace App\Enums;

enum UtilityAllocationStatus: string
{
    case Calculated = 'calculated';
    case Invoiced = 'invoiced';

    public function label(): string
    {
        return match ($this) {
            self::Calculated => 'Calculated',
            self::Invoiced => 'Invoiced',
        };
    }
}
