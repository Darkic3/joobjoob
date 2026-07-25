<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_admin == 1) {

            $user = Auth::user();
            $currentRoute = Route::currentRouteName();

            if ($currentRoute == 'admin.dashboard.index') {
                return $next($request);
            }

            if ($user->hasAccess($currentRoute)) {
                return $next($request);
            }

            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }
            return redirect()->route('admin.dashboard.index')
                ->with('error', 'شما به این بخش دسترسی ندارید.');

        }
        return redirect()->route('admin.login');
    }
}
