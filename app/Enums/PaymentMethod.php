<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Gcash = 'gcash';
    case Paymaya = 'paymaya';
    case Check = 'check';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank transfer',
            self::Gcash => 'GCash',
            self::Paymaya => 'Maya',
            self::Check => 'Check',
            self::Other => 'Other',
        };
    }
}
