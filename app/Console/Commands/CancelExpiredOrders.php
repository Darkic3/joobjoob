<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\StockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired {--minutes=15 : تعداد دقیقه‌های انتظار} {--dry-run : فقط نمایش بدون اجرا}';
    protected $description = 'لغو خودکار سفارشات منقضی شده (status=paying) و بازگردانی موجودی';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subMinutes($minutes);

        $expiredOrders = Order::where('order_status', 'paying')
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info("هیچ سفارش منقضی شده‌ای یافت نشد.");
            return Command::SUCCESS;
        }

        $this->info("{$expiredOrders->count()} سفارش منقضی شده یافت شد:");

        $orderService = app(\App\Services\OrderService::class);
        $cancelled = 0;
        $failed = 0;

        foreach ($expiredOrders as $order) {
            $this->line("  - سفارش #{$order->id} | کاربر: {$order->user_id} | تاریخ: {$order->created_at->format('Y-m-d H:i')} | مبلغ: " . number_format($order->payment_price));

            if ($dryRun) {
                $this->line("    [DRY-RUN] لغو نشد.");
                continue;
            }

            try {
                $previousStatus = $order->order_status;
                $orderService->updateOrderStatus($order, 'cancelled', 'سیستم - سفارش منقضی');

                Log::info("Auto-cancelled expired order #{$order->id}", [
                    'previous_status' => $previousStatus,
                    'created_at' => $order->created_at,
                    'payment_price' => $order->payment_price,
                ]);

                $cancelled++;
            } catch (\Exception $e) {
                Log::error("Failed to auto-cancel order #{$order->id}", [
                    'message' => $e->getMessage(),
                ]);
                $failed++;
                $this->error("  ✗ خطا در لغو سفارش #{$order->id}: {$e->getMessage()}");
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("نتیجه: {$cancelled} سفارش لغو شد" . ($failed > 0 ? "، {$failed} خطا" : ""));
        }

        return Command::SUCCESS;
    }
}
