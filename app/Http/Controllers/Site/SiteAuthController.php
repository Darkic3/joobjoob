<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class SiteAuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('site.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return redirect()->intended(localizedRoute('dashboard'));
        }

        return back()->withErrors(['email' => __('messages.login_failed')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->clearRecallerCookie();

        return redirect()->route('home');
    }

    private function clearRecallerCookie(): void
    {
        $guard = Auth::guard();
        $name  = 'remember_web_'.$guard->getName().'_'.md5($guard::class);

        if (Cookie::hasQueued($name)) {
            Cookie::queue(Cookie::forget($name));
        }
    }
}
