<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Order;
use App\Payments\PaymentGatewayFactory;
use App\Services\StockService;
use App\Services\AuditService;
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
            // P0-03 Fix + rework: use centralized status transition so stock logic is consistent
            try {
                $orderService = app(\App\Services\OrderService::class);
                $orderService->updateOrderStatus($order, 'unpaid', 'Gateway token failed');
            } catch (\Throwable $e) {
                Log::error('updateOrderStatus unpaid failed on token failure for order #' . $order->id, [
                    'message' => $e->getMessage(),
                ]);
                // fallback: ensure status is unpaid even if stock logic threw
                if ($order->order_status !== 'unpaid') {
                    $order->update(['order_status' => 'unpaid']);
                    try { AuditService::orderStatusChanged($order, $order->order_status, 'unpaid', 'Gateway token failed'); } catch (\Throwable $ex) { Log::error('AuditLog failed', ['message' => $ex->getMessage()]); }
                }
            }

            return redirect(route('checkout.failed'))
                ->with('error', 'خطا در ارتباط با درگاه پرداخت، لطفاً مجدداً تلاش کنید.');
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
            // وضعیت را اینجا تغییر نمی‌دهیم — caller (OrderService::callback -> handleFailedPayment) مسئول تغییر وضعیت و بازگردانی موجودی است
            // تا از double-restore و لاگ ناقص جلوگیری شود. فقط verify data را ذخیره می‌کنیم (بالا).
            return false;
        }

        return true;
    }

    /**
     * Resolve StockService from the container.
     */
    private function stockService(): StockService
    {
        return app(StockService::class);
    }
}
