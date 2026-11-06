<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
