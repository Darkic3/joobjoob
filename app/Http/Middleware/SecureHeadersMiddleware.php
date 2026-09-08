<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers middleware — adds OWASP recommended headers
 * to protect against Clickjacking, MIME Sniffing, and other attacks.
 */
class SecureHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking — only allow same-origin iframes
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing — browser must respect Content-Type
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Control referrer information — send origin only on cross-origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict browser features — disable camera, microphone, geolocation by default
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS — force HTTPS for 1 year (only on secure connections or production)
        if ($request->isSecure() || config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
