<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        // کاربرِ از قبل لاگین‌شده نیازی به دیدن فرم لاگین ندارد
        if (auth()->check()) {
            return redirect()->route('admin.dashboard.index');
        }

        return view('admin.auth.login');
    }

    public function postLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $user = Auth::user();

            // چک «ادمین واقعی»: سوپرادمین یا حداقل یک نقش.
            // پیام یکسان برای همه خطاها تا اطلاعات لو نرود.
            if (! $user->is_admin && ! $user->roles()->exists()) {
                Auth::logout();

                return back()->withErrors(['email' => __('admin.login.restricted')])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard.index'));
        }

        return back()->withErrors(['email' => __('admin.login.failed')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
