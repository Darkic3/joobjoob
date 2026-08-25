<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Order;
use App\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class BankPaymentService
{
    /**
     * هدایت کاربر به درگاه پرداخت
     */
    public function handlePayment(Bank $bank, Order $order, int $price): RedirectResponse
    {
        $gateway = PaymentGatewayFactory::create($bank, config('app.name', 'Shop'));

        $response = $gateway->getBankToken($price, $order);

        Auth::loginUsingId($order->user_id);

        if ($response->getStatus() === 'failed') {
            $order->delete();
            return redirect(route('checkout.failed'))
                ->with('error', 'خطا در پرداخت، مجدد تلاش نمایید.');
        }

        $order->update([
            'transaction_info' => [
                'post' => $response->getPostData(),
                'verify' => [],
            ],
        ]);

        ob_start();
        $gateway->redirect($response, $order);
        $content = ob_get_clean();

        return response($content);
    }

    /**
     * تأیید تراکنش پس از بازگشت از درگاه
     */
    public function handleVerify(Order $order, array $callbackData, int $price): bool
    {
        $gateway = PaymentGatewayFactory::create($order->bank, config('app.name', 'Shop'));

        $response = $gateway->verifyTransaction($callbackData, $price, $order);

        Auth::loginUsingId($order->user_id);

        $transactionInfo = $order->transaction_info ?? [];
        $transactionInfo['verify'] = $response->getVerifyData() ?? [];

        $order->update([
            'transaction_info' => $transactionInfo,
        ]);

        if ($response->getStatus() === 'failed') {
            $order->update(['order_status' => 'unpaid']);
            return false;
        }

        return true;
    }
}
