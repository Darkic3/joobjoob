<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('roles')
            ->with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->string('search'));
                $q->where(function ($w) use ($s) {
                    $w->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('mobile', 'like', "%{$s}%");
                });
            })
            // ⚠️ به‌جای filled('admin') — چون empty("0") در PHP true است و فیلتر «کاربران عادی» را بی‌اثر می‌کرد
            ->when(in_array($request->input('admin'), ['0', '1'], true), function ($q) use ($request) {
                $q->where('is_admin', $request->input('admin') === '1');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('is_system', 'desc')->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $isSuperAdmin = auth()->user()->is_admin;

        $data = $request->safe()->except(['password', 'roles', 'is_admin', 'is_active']);
        $data['password'] = $request->string('password');

        // 🔒 فقط سوپرادمین می‌تواند ادمین بسازد؛ کاربر با دسترسی users.create
        // فقط کاربر عادی و فعال می‌سازد (هرگز نمی‌تواند is_admin/is_active را جعل کند)
        $data['is_admin'] = $isSuperAdmin && $request->boolean('is_admin');
        $data['is_active'] = $isSuperAdmin ? $request->boolean('is_active', true) : true;

        $user = User::create($data);

        // 🔒 غیر سوپرادمین نمی‌تواند نقش سیستمی (مثل main با همه دسترسی‌ها) بدهد
        // — وگرنه با users.create می‌توانست بدون سلب مقام، دسترسی کامل بسازد
        $roleIds = $request->input('roles', []);
        if (! $isSuperAdmin) {
            $roleIds = Role::whereIn('id', $roleIds)->where('is_system', false)->pluck('id')->all();
        }
        $user->roles()->sync($roleIds);

        // مسیر بازگشت: اولویت با لیست کاربران است (طبق تجربه کاربری درخواستی)
        // — تا اگر دسترسی index ندارد به 403 نخورد، به داشبورد برمی‌گردد.
        $redirectTarget = $isSuperAdmin || auth()->user()->canAccess('admin.users.index')
            ? 'admin.users.index'
            : 'admin.dashboard.index';

        return redirect()->route($redirectTarget)->with('success', 'کاربر با موفقیت ساخته شد.');
    }

    public function edit(User $user): View
    {
        // هر کاربری که خودش سوپرادمین نیست نمی‌تواند سوپرادمین‌ها را ببیند/ویرایش کند
        abort_if(! auth()->user()->is_admin && $user->is_admin, 403);

        $user->load('roles');
        $roles = Role::orderBy('is_system', 'desc')->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        // 🔒 همان محافظت edit: غیر سوپرادمین حتی نباید بتواند سوپرادمین را ویرایش کند
        // (بدون این، کاربر با دسترسی users.edit می‌توانست مستقیم PUT بزند و رمز/ایمیل/نقش سوپرادمین را تغییر دهد)
        abort_if(! auth()->user()->is_admin && $user->is_admin, 403);

        // فقط سوپرادمین می‌تواند وضعیت is_admin را تغییر دهد
        $canChangeAdminFlag = auth()->user()->is_admin;

        $data = $request->safe()->except(['password', 'roles', 'is_admin', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = $request->string('password');
        }

        // تغییر وضعیت‌های حساس فقط برای سوپرادمین — همراه با محافظت از آخرین سوپرادمین
        if ($canChangeAdminFlag) {
            $newAdmin = $request->boolean('is_admin');
            $newActive = $request->boolean('is_active');

            // محافظت از آخرین سوپرادمین فعال: نمی‌توان is_admin یا is_active او را خاموش کرد
            $isLastActiveSuperAdmin = $user->is_admin
                && $user->is_active
                && User::where('is_admin', true)->where('is_active', true)->count() <= 1;

            if ($isLastActiveSuperAdmin && (! $newAdmin || ! $newActive)) {
                return back()
                    ->withErrors(['is_admin' => 'آخرین مدیر ارشد فعال را نمی‌توان غیرفعال کرد.'])
                    ->withInput();
            }

            $data['is_admin'] = $newAdmin;
            $data['is_active'] = $newActive;
        }

        $user->update($data);

        // 🔒 غیر سوپرادمین نمی‌تواند نقش سیستمی (main) به کاربر بدهد — جلوگیری از ارتقاء دسترسی
        $roleIds = $request->input('roles', []);
        if (! $canChangeAdminFlag) {
            $roleIds = Role::whereIn('id', $roleIds)->where('is_system', false)->pluck('id')->all();
        }
        $user->roles()->sync($roleIds);

        // دسترسی‌های این کاربر (و همه کاربران با نقش‌هایش) فوراً تازه شوند
        User::bumpPermissionsVersion();

        // اگر خودِ مدیر، مقام/فعالیت خودش را سلب کرد (در حالی که ادمین دیگری هست)،
        // دیگر به صفحه ویرایش دسترسی ندارد → به مسیر امن (داشبورد) برو
        $losesOwnAccess = $user->is(auth()->user())
            && $canChangeAdminFlag
            && (! $user->fresh()->is_admin || ! $user->fresh()->is_active);

        $redirectTarget = $losesOwnAccess ? 'admin.dashboard.index' : 'admin.users.edit';

        return redirect()->route($redirectTarget, $losesOwnAccess ? [] : [$user])
            ->with('success', 'کاربر با موفقیت به‌روزرسانی شد.');
    }
}
