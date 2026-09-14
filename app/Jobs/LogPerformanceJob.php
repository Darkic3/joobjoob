<?php

namespace App\Jobs;

use App\Models\RequestPerformanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job برای ثبت غیرهمزمان لاگ پرفورمانس درخواست‌ها.
 */
class LogPerformanceJob implements ShouldQueue
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
        RequestPerformanceLog::create($this->data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('LogPerformanceJob failed', [
            'data'    => $this->data,
            'message' => $exception->getMessage(),
        ]);
    }
}
