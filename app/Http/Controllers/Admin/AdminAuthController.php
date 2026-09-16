<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

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

        try {
            $authenticated = Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'));
        } catch (\RuntimeException $e) {
            // هش ذخیره‌شده با الگوریتم bcrypt لاراول ساخته نشده
            // (مثلاً $2a$ به‌جای $2y$) — BcryptHasher در این حالت استثنا می‌دهد.
            // به‌جای خطای ۵۰۰، مثل «رمز اشتباه» رفتار کن تا پیام یکسان بماند
            // و اطلاعات لو نرود؛ بقیه RuntimeExceptionها باید بالا بروند.
            if (! str_contains($e->getMessage(), 'Bcrypt')) {
                throw $e;
            }

            $authenticated = false;
        }

        if ($authenticated) {
            $user = Auth::user();

            // چک «ادمین واقعی»: سوپرادمین یا حداقل یک نقش.
            // پیام یکسان برای همه خطاها تا اطلاعات لو نرود.
            if (! $user->is_admin && ! $user->roles()->exists()) {
                Auth::logout();

                return back()->withErrors(['email' => __('admin.login.restricted')])->onlyInput('email');
            }

            return redirect()->intended(route('admin.dashboard.index'));
        }

        return back()->withErrors(['email' => __('admin.login.failed')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // پاک‌سازی کوکی «مرا به خاطر بسپار» — بدون این خط، کوکی باقی می‌ماند
        // و کاربر در درخواست بعدی دوباره به‌صورت خودکار وارد می‌شود.
        $this->clearRecallerCookie();

        return redirect()->route('admin.login');
    }

    /**
     * حذف کوکی recaller (remember-me) مرتبط با guard وب.
     */
    private function clearRecallerCookie(): void
    {
        $guard = Auth::guard();
        $name  = 'remember_web_'.$guard->getName().'_'.md5($guard::class);

        if (Cookie::hasQueued($name)) {
            Cookie::queue(Cookie::forget($name));
        }
    }
}
