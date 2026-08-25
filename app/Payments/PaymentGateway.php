<?php

namespace App\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * دریافت توکن از درگاه پرداخت
     */
    public function getBankToken(int $amount, Order $order): PaymentPostDTO;

    /**
     * تأیید تراکنش پس از بازگشت از درگاه
     */
    public function verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO;

    /**
     * ریدایرکت کاربر به درگاه پرداخت
     */
    public function redirect(PaymentPostDTO $postData, Order $order): void;
}
