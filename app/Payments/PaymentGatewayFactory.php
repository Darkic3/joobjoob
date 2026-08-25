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
            default => throw new \InvalidArgumentException("Unsupported bank type: {$bank->bank_type}"),
            // آینده: sep, sadad, zibal, snappay, ...
        };
    }
}
