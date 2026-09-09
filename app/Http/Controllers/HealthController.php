<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): Response
    {
        $checks = [];
        $healthy = true;

        // Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'error: '.$e->getMessage();
            $healthy = false;
        }

        // Cache
        try {
            Cache::store('default')->put('health_check', true, 10);
            $checks['cache'] = 'ok';
        } catch (\Throwable $e) {
            $checks['cache'] = 'error: '.$e->getMessage();
            $healthy = false;
        }

        $status = $healthy ? 200 : 503;

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $status);
    }
}
