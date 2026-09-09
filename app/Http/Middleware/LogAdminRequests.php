<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogAdminRequests
{
    private const SENSITIVE_FIELDS = [
        'password', 'password_confirmation', '_token',
        'receipt_tracking_code', 'receipt_payer_card',
        'card_number', 'mobile', 'email',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            $payload = $request->input();

            foreach (self::SENSITIVE_FIELDS as $field) {
                unset($payload[$field]);
            }

            RequestLog::create([
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'status_code' => $response->getStatusCode(),
                'payload' => $payload,
            ]);
        }

        return $response;
    }
}
