<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Check = 'check';
    case CreditCard = 'credit_card';
    case BankTransfer = 'bank_transfer';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Check => 'Check',
            self::CreditCard => 'Credit Card',
            self::BankTransfer => 'Bank Transfer',
            self::Credit => 'Credit',
        };
    }
}
