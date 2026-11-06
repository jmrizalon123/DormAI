<?php

namespace App\Enums;

enum PlatformContactMessageStatus: string
{
    case Pending = 'pending';
    case Read = 'read';
    case Replied = 'replied';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'New',
            self::Read => 'Read',
            self::Replied => 'Replied',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'primary',
            self::Read => 'info',
            self::Replied => 'success',
            self::Closed => 'secondary',
        };
    }
}
