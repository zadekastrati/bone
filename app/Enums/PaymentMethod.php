<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CashOnDelivery = 'cash_on_delivery';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => __('Bank transfer'),
            self::CashOnDelivery => __('Cash on delivery'),
            self::Card => __('Card (Visa/Mastercard)'),
        };
    }
}
