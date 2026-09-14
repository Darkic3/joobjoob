<?php

namespace App\Jobs;

use App\Models\SmsLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job برای ثبت غیرهمزمان لاگ SMS.
 */
class LogSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 15;

    public function __construct(
        public array $data,
    ) {}

    public function handle(): void
    {
        SmsLog::create($this->data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('LogSmsJob failed', [
            'data'    => $this->data,
            'message' => $exception->getMessage(),
        ]);
    }
}
