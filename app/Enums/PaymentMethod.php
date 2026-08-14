<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CashOnDelivery = 'cash_on_delivery';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => __('Bank transfer'),
            self::CashOnDelivery => __('Cash on delivery'),
        };
    }
}
