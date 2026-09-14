<?php

namespace App\Jobs;

use App\Models\PaymentLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job برای ثبت غیرهمزمان لاگ پرداخت.
 *
 * از طریق PaymentLogService dispatch می‌شود و در queue=log اجرا می‌شود.
 */
class LogPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 15;

    public string $queue = 'default';

    public function __construct(
        public array $data,
    ) {}

    public function handle(): void
    {
        PaymentLog::create($this->data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('LogPaymentJob failed', [
            'data'    => $this->data,
            'message' => $exception->getMessage(),
        ]);
    }
}
