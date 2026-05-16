<?php

namespace App\Service;

use App\Entity\Orders;
use App\Enum\PaymentMethod;

class PaymentService
{
    public function process(Orders $order, PaymentMethod $method): array
    {
        return match ($method) {

            PaymentMethod::CashOnDelivery => [
                'success' => true,
                'orderId' => $order->getId(),
                'payment' => 'cash_on_delivery'
            ],

            PaymentMethod::Wave => [
                'redirectUrl' => $this->waveProvider($order),
            ],

            PaymentMethod::OrangeMoney => [
                'redirectUrl' => $this->orangeMoneyProvider($order),
            ],

            PaymentMethod::Card => [
                'redirectUrl' => $this->cardProvider($order),
            ],
        };
    }

    private function waveProvider(Orders $order): string
    {
        return "https://wave.com/pay?order=" . $order->getId();
    }

    private function orangeMoneyProvider(Orders $order): string
    {
        return "https://orange-money.com/pay?order=" . $order->getId();
    }

    private function cardProvider(Orders $order): string
    {
        return "https://stripe.com/pay?order=" . $order->getId();
    }
}