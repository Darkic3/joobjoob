<?php

namespace App\Payments;

use App\Models\Bank;

class PaymentGatewayFactory
{
    /**
     * ساخت درگاه پرداخت بر اساس نوع بانک
     *
     * @throws \InvalidArgumentException
     */
    public static function create(Bank $bank, string $siteName = ''): PaymentGateway
    {
        return match ($bank->bank_type) {
            'zarinPal' => new ZarinPal($bank, $siteName),
            'card_to_card' => new ZarinPal($bank, $siteName), // card_to_card handled separately in OrderService
            default => throw new \InvalidArgumentException("Unsupported bank type: {$bank->bank_type}"),
        };
    }
}
