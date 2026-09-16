<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;

interface PaymentGateway
{
    /**
     * دریافت توکن از درگاه پرداخت
     *
     * @param  int  $amount  مبلغ به واحد پولی فروشگاه (تومان)
     */
    public function getBankToken(int $amount, Order $order): PaymentPostDTO;

    /**
     * تأیید تراکنش پس از بازگشت از درگاه
     *
     * @param  int  $price  مبلغ سفارش به واحد پولی فروشگاه (تومان) — همیشه از دیتابیس
     */
    public function verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO;

    /**
     * ساخت URL هدایت به درگاه پرداخت
     */
    public function getRedirectUrl(PaymentPostDTO $postData): string;

    /**
     * ریدایرکت کاربر به درگاه پرداخت (پاسخ لاراولی — بدون header/exit خام)
     */
    public function redirect(PaymentPostDTO $postData, Order $order): RedirectResponse;
}
