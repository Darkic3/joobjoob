<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Auth;

class LogAdminRequests
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {

            $payload = $request->input();

            unset($payload['password']);
            unset($payload['password_confirmation']);
            unset($payload['_token']);

            RequestLog::create([
                'user_id'     => Auth::id(),
                'method'      => $request->method(),
                'url'         => $request->fullUrl(),
                'ip'          => $request->ip(),
                'status_code' => $response->getStatusCode(),
                'payload'     => $payload, // مدل cast می‌کند (json) — بدون json_encode دستی
            ]);
        }

        return $response;
    }
}
