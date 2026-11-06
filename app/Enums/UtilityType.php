<?php

namespace App\Enums;

enum UtilityType: string
{
    case Electricity = 'electricity';
    case Water = 'water';

    public function label(): string
    {
        return match ($this) {
            self::Electricity => 'Electricity',
            self::Water => 'Water',
        };
    }

    public function defaultUnit(): string
    {
        return match ($this) {
            self::Electricity => 'kWh',
            self::Water => 'cu.m',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Electricity => 'bi-lightning-charge',
            self::Water => 'bi-droplet',
        };
    }
}
