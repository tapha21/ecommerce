<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case Wave = 'wave';
    case OrangeMoney = 'orange_money';
    case Card = 'card';
    case CashOnDelivery = 'cash_on_delivery';

    public function label(): string
    {
        return match ($this) {
            self::Wave => 'Wave',
            self::OrangeMoney => 'Orange Money',
            self::Card => 'Carte bancaire',
            self::CashOnDelivery => 'À la livraison',
        };
    }
}
