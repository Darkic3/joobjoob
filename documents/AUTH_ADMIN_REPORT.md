# گزارش طراحی و پیاده‌سازی RBAC — نسخه بهینه (بلوپرینت قابل استفاده مجدد)

> این سند، **بلوپرینت نهایی** سیستم احراز هویت و سطح دسترسی پنل ادمین است که ابتدا روی پروژه فروشگاهی **Shopora** پیاده می‌شود و بعداً برای هر سایت Laravel دیگری با کمترین تغییر قابل انتقال است.
>
> این نسخه، بازسازی بهینه‌ی یک دیزاین قدیمی‌تر (RBAC مبتنی بر `serialize()` نام روت داخل جدول `roles`) است. دلایل هر تصمیم و تفاوت‌ها در بخش ۲ آمده است.
>
> وضعیت: **طراحی آماده، منتظر پیاده‌سازی.** سیدر `AdminOwnerSeeder` هم‌اکنون موجود است و به محض اجرای میگریشن‌ها، ادمین اصلی را می‌سازد.

---

## فهرست

1. [خلاصه اجرایی](#۱-خلاصه-اجرایی)
2. [چرا این نسخه؟ (مقایسه با دیزاین قدیمی)](#۲-چرا-این-نسخه-مقایسه-با-دیزاین-قدیمی)
3. [نمای کلی معماری](#۳-نمای-کلی-معماری)
4. [دیتابیس — میگریشن‌ها](#۴-دیتابیس--میگریشنها)
5. [مدل‌ها](#۵-مدلها)
6. [کانفیگ درختی دسترسی‌ها](#۶-کانفیگ-درختی-دسترسیها)
7. [میدلور AdminPermission](#۷-میدلور-adminpermission)
8. [جریان لاگین ادمین](#۸-جریان-لاگین-ادمین)
9. [روت‌های ادمین (ساختار نهایی)](#۹-روتهای-ادمین-ساختار-نهایی)
10. [UI مدیریت نقش‌ها و کاربران](#۱۰-ui-مدیریت-نقشها-و-کاربران)
11. [سیدرها (AdminOwnerSeeder)](#۱۱-سیدرها-adminownerseeder)
12. [چک‌لیست امنیتی — باید و نباید](#۱۲-چکلیست-امنیتی--باید-و-نباید)
13. [راهنمای انتقال به سایت دیگر (Porting Guide)](#۱۳-راهنمای-انتقال-به-سایت-دیگر-porting-guide)
14. [مسیر آینده (گام‌های بعدی)](#۱۴-مسیر-آینده-گامهای-بعدی)
15. [نقشه راه پیاده‌سازی — فازبندی ۰→۱۰۰](#۱۵-نقشه-راه-پیادهسازی--فازبندی-۰۱۰۰)

---

## ۱) خلاصه اجرایی

- **اصل طراحی:** «کلید دسترسی = نام روت» (`admin.products.edit`). این ساده‌ترین، خواناترین و دقیقاً سازگار با پنل‌های Laravel است.
- **دانه‌بندی:** سطح اکشن (مشاهده/افزودن/ویرایش/حذف برای هر ماژول، جدا از هم).
- **چهار جدول:** `roles`، `permissions`، `permission_role`، `role_user` + یک ستون `is_admin` روی `users`.
- **سوپرادمین:** ستون `users.is_admin = 1` → بدون هیچ چکی از همه‌چیز عبور می‌کند. **هیچ‌وقت** به دسترسی‌ها گره نمی‌خورد (هرگز خودتان را قفل نمی‌کنید).
- **منبع واحد حقیقت:** کانفیگ درختی `config/permissions.php` — هم UI چک‌باکس‌ها را می‌سازد، هم نقش `main` (دسترسی کامل) را seed می‌کند، هم نام روت‌ها از آن استخراج می‌شوند.
- **بدون پکیج خارجی** برای هسته (در بخش ۱۴، گزینه ارتقا به Spatie بررسی شده).
- **بدون JWT** در نسخه فعلی (پنل Vue نداریم). مسیر اضافه‌کردن بعداً در بخش ۱۴ مستند شده.
- **امنیت:** throttle روی لاگین، چک «ادمین واقعی» در خود لاگین، کش دسترسی‌ها با invalidation، روت‌های آزاد فقط لیست سفید صریح.
- **چندزبانه:** عناوین دسترسی‌ها از فایل‌های `lang/*/permissions.php` خوانده می‌شوند.

---

## ۲) چرا این نسخه؟ (مقایسه با دیزاین قدیمی)

دیزاین قدیمی: جدول `roles` با ستون `permission` (TEXT) که `serialize()` آرایه‌ای از نام روت‌ها بود + جدول `user_types` برای تشخیص ادمین + پنل JWT جدا.

| موضوع | دیزاین قدیمی | نسخه بهینه | دلیل |
|---|---|---|---|
| ذخیره دسترسی‌ها | آرایه serialize داخل ستون roles | **جدول `permissions` + پایوت `permission_role`** | قابل query، ممیزی («کدام نقش‌ها حذف محصول دارند؟»)، FK واقعی، بدون پوسیدگی فرمت |
| تفکیک ادمین | جدول `user_types` (enum) | **ستون `is_admin` بولی** | یک بعد کمتر؛ ساده‌تر؛ سوپرادمین از قبل توی مدل موجود بود |
| پنل API | گارد `admin_jwt` (tymon/jwt-auth) | **حذف (فعلاً)** | پنل Vue/API وجود ندارد؛ اضافه‌کردن آن مستند شده است |
| تعداد گروه‌ها | ۵۲ گروه (دوره، گالری، جلسه…) | **~۱۴ گروه واقعی پروژه** | بدون سربار ماژول‌های ناموجود |
| کش دسترسی‌ها | بدون کش (unserialize در هر ریکوئست) | **Cache با invalidation روی تغییر نقش** | عملکرد بهتر |
| نقش full-access | کلید بی‌اثر `fullAccess` + hardcode داشبورد | **نقش سیستمی `main` (is_system=1)** + سوپرادمین | معنادار و قابل اجرا |
| لاگین | فقط `Auth::attempt` بدون چک ادمین، بدون throttle | **چک نقش/ادمین + throttle + بررسی is_active** | امن و بدون لو دادن اطلاعات |
| عنوان دسترسی‌ها | فارسی هاردکد در کانفیگ | **کلید ترجمه از lang/** | چندزبانه (مطابق پنل چندزبانه پروژه) |

> ⚠️ باگ‌های امنیتی دیزاین قدیمی (بک‌دور `/login_admin/{token}`، `JWT_SECRET` هاردکد، `str_contains` معکوس، OTP بدون انقضا و…) در بخش ۱۲ به‌عنوان «نبایدها» فهرست شده‌اند.

---

## ۳) نمای کلی معماری

```
┌─────────────────────────────────────────────┐
│                  users                       │  ← یک مدل برای همه
│  id, name, mobile, email, password, is_active│
│  is_admin (boolean) ← سوپرادمین بای‌پس       │
│  deleted_at (SoftDeletes)                    │
└──────────────┬──────────────────────────────┘
               │ 1                N
┌──────────────▼────────┐   ┌──────────────────┐
│       role_user       │   │     roles        │
│   user_id, role_id    │──▶│ id, name,        │
└──────────────┬────────┘   │ is_system (main) │
               │ 1          └────────┬─────────┘
               │                N    │
┌──────────────▼────────┐   ┌────────▼─────────┐
│   permission_role     │   │   permissions    │
│  role_id, perm_id     │◀──│ id, name (روت),  │
└───────────────────────┘   │ group, label_key │
                            └──────────────────┘
```

- **گارد:** فقط `web` (session). همه‌چیز روی یک جدول `users`.
- **دو سطح مجوز:**
  1. `is_admin = 1` → سوپرادمین، عبور بدون چک.
  2. در غیر این صورت → جمع همه دسترسی‌های همه نقش‌های کاربر، چک عضویت نام روت جاری.
- **جریان درخواست:** `web` → `SetLocale`/`SetTheme` (گلوبال) → `auth` → `adminPermission` → `log.admin` → کنترلر.

---

## ۴) دیتابیس — میگریشن‌ها

> نام فایل‌ها با تاریخ اجرای واقعی جلو می‌روند. این‌ها را به‌ترتیب اجرا کنید.

### ۴.۱) افزودن ستون سوپرادمین به users

```php
// database/migrations/2026_08_06_000001_add_is_admin_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false)->after('is_active');
});
```

### ۴.۲) جدول roles

```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();              // main, content-manager, support
    $table->boolean('is_system')->default(false);  // نقش سیستمی (main) قابل حذف نیست
    $table->timestamps();
});
```

### ۴.۳) جدول permissions (کاتالوگ دسترسی‌ها)

```php
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();            // admin.products.edit
    $table->string('group')->index();            // product (برای گروه‌بندی UI)
    $table->string('label_key')->nullable();     // permissions.access.product.edit
    $table->timestamps();
});
```

### ۴.۴) جدول واسط permission_role

```php
Schema::create('permission_role', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
    $table->primary(['role_id', 'permission_id']);
});
```

### ۴.۵) جدول واسط role_user

```php
Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->primary(['user_id', 'role_id']);
});
```

---

## ۵) مدل‌ها

### ۵.۱) Role

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
```

### ۵.۲) Permission

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'group', 'label_key'];
}
```

### ۵.۳) User — ترِیت HasAdminPermissions

```php
<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait HasAdminPermissions
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /** نام همه دسترسی‌های همه نقش‌های کاربر (کش‌شده) */
    public function permissionNames(): array
    {
        return Cache::remember(
            "admin.permissions.{$this->id}",
            now()->addHour(),
            fn () => $this->roles
                ->loadMissing('permissions')
                ->flatMap(fn ($role) => $role->permissions)
                ->pluck('name')
                ->unique()
                ->values()
                ->all()
        );
    }

    /** آیا به این روت دسترسی دارد؟ سوپرادمین همیشه true */
    public function canAccess(string $routeName): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return in_array($routeName, $this->permissionNames(), true);
    }

    /** بعد از هر تغییر نقش/دسترسی، کش را پاک کن */
    public function forgetPermissionCache(): void
    {
        Cache::forget("admin.permissions.{$this->id}");
    }
}
```

> در `User` مدل: `use HasAdminPermissions;` — متد قدیمی `hasAccess(string $routeName)` با `canAccess()` جایگزین می‌شود.

---

## ۶) کانفیگ درختی دسترسی‌ها

**منبع واحد حقیقت.** فایل `config/permissions.php`:

```php
<?php

// درخت دسترسی‌های پنل ادمین
// کلید نهایی هر دسترسی = "admin.{group}.{access}" (نام روت واقعی)
// مقدار هر دسترسی = کلید ترجمه (lang/*/permissions.php)
return [

    // روت‌هایی که فقط «احراز هویت» می‌خواهند، نه دسترسی خاص
    // (آپلود CKEditor، خروج و داشبورد — هر ادمین لاگین‌شده باید به آن‌ها دسترسی داشته باشد؛
    //  داشبورد صرفاً نمای کلی است و قدرت تخریبی ندارد)
    'free' => [
        'admin.ckeditor.upload',
        'admin.logout',
        'admin.dashboard.index',
    ],

    'permissions' => [

        // ⚠️ گروه dashboard عمداً در درخت نیست — چون admin.dashboard.index در لیست free است
        // و هر ادمینِ لاگین‌شده آن را می‌بیند (نمای کلی، بدون قدرت تخریبی).
        // ⚠️ کلید هر گروه باید دقیقاً بخش نام روت واقعی باشد (جمع، مثل Route::resource)
        'products' => [
            'title'  => 'permissions.groups.products',
            'access' => [
                'index'   => 'permissions.access.products.index',
                'create'  => 'permissions.access.products.create',
                'store'   => 'permissions.access.products.create',   // همان لیبل → UI dedupe می‌کند
                'edit'    => 'permissions.access.products.edit',
                'update'  => 'permissions.access.products.edit',
                'destroy' => 'permissions.access.products.delete',
            ],
        ],

        'product-categories' => [ /* مثل products → admin.product-categories.* */ ],
        'brands'             => [ /* مثل products → admin.brands.* */ ],
        'specifications'     => [ /* مثل products → admin.specifications.* */ ],

        'blogs'          => [ /* مثل products → admin.blogs.* */ ],
        'blog-categories' => [ /* مثل products → admin.blog-categories.* */ ],

        'hero-slides'  => [ /* مثل products → admin.hero-slides.* */ ],
        'trust-badges' => [ /* مثل products → admin.trust-badges.* */ ],

        'settings' => [
            'title'  => 'permissions.groups.settings',
            'access' => [
                'edit'   => 'permissions.access.settings.edit',
                'update' => 'permissions.access.settings.edit',
            ],
        ],

        // ⏳ گروه‌های آینده (از الان در درخت، تا نقش main خودکار پوشش دهد)
        'users'  => ['title' => 'permissions.groups.users',  'access' => [/* index, create, edit, destroy */]],
        'roles'  => ['title' => 'permissions.groups.roles',  'access' => [/* index, create, edit, destroy */]],
        'orders' => ['title' => 'permissions.groups.orders', 'access' => [/* index, show */]],
        'reviews'=> ['title' => 'permissions.groups.reviews','access' => [/* index, destroy */]],
    ],
];
```

**قوانین کانفیگ:**
- نام روت واقعی در `routes/admin.php` باید دقیقاً برابر `admin.{group}.{access}` باشد — پس **کلید گروه باید همان بخش نام روت باشد** (برای `Route::resource('products', ...)` کلید گروه = `products`، نه `product`).
- هر روت ادمین خارج از این الگو را صریحاً به `free` اضافه کنید (فقط اگر احراز هویت برایش کافی است) یا یک گروه مناسب برایش بسازید.
- `store`/`update` لیبل مشترک با `create`/`edit` دارند؛ **UI باید بر اساس `label_key` dedupe کند** (چک‌باکس‌های `create` و `store` با هم toggle می‌شوند).
- `store`/`update` معمولاً با `create`/`edit` یک دسترسی مشترک دارند (تا ادمین مجبور نباشد دو چک‌باکس جدا تیک بزند).
- افزودن گروه جدید = فقط یک بلوک به کانفیگ + یک بلاک به فایل‌های ترجمه. نقش `main` با سیدر خودکار به‌روز می‌شود.

### ۶.۱) کانفیگ اعتبارنامه ادمین اصلی — `config/admin.php`

```php
<?php

// اعتبارنامه ادمین اصلی (سیدر AdminOwnerSeeder از همین‌جا می‌خواند)
return [
    'owner' => [
        'name'     => env('ADMIN_OWNER_NAME', 'مدیر اصلی'),
        'email'    => env('ADMIN_OWNER_EMAIL', 'admin@shopora.test'),
        'mobile'   => env('ADMIN_OWNER_MOBILE', '09120000000'),
        'password' => env('ADMIN_OWNER_PASSWORD'), // null → رمز تصادفی یک‌باره
    ],
];
```

> ⚠️ سیدر هرگز مستقیم `env()` نمی‌خواند (بعد از `php artisan config:cache` مقدار `env()` خارج از فایل‌های کانفیگ null است) — همیشه از `config('admin.owner.*')`.

**فایل ترجمه** `resources/lang/{fa,en,ar,tr}/permissions.php`:

```php
return [
    // کلیدها باید با کلیدهای گروه کانفیگ یکی باشند (جمع)
    'groups' => [
        'dashboard' => 'داشبورد',
        'products'  => 'محصولات',
        // ...
    ],
    'access' => [
        'dashboard' => ['index' => 'مشاهده داشبورد'],
        'products'  => [
            'index'  => 'مشاهده محصولات',
            'create' => 'افزودن محصول',
            'edit'   => 'ویرایش محصول',
            'delete' => 'حذف محصول',
        ],
        // ...
    ],
];
```

---

## ۷) میدلور AdminPermission

بازنویسی کامل `app/Http/Middleware/AdminPermission.php` (نسخه فعلی کامنت‌شده و بی‌اثر است):

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // ۱) لاگین نیست → هدایت به لاگین (یا 401 برای AJAX)
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => __('admin.login.restricted')], 401)
                : redirect()->guest(route('admin.login'));
        }

        // ۲) سوپرادمین → عبور بدون چک (هرگز قفل نمی‌شود)
        if ($user->is_admin) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // ۳) روت‌های لیست سفید (فقط احراز هویت)
        if ($routeName && in_array($routeName, config('permissions.free', []), true)) {
            return $next($request);
        }

        // ۴) چک دسترسی
        abort_unless($routeName && $user->canAccess($routeName), 403);

        return $next($request);
    }
}
```

- ثبت alias قبلاً در `bootstrap/app.php` انجام شده: `'adminPermission' => AdminPermission::class`.
- کش دسترسی‌ها داخل `permissionNames()` است؛ بعد از هر تغییر نقش (`roles()->sync` و…) حتماً `forgetPermissionCache()` صدا زده شود.

---

## ۸) جریان لاگین ادمین

**کنترلر** `app/Http/Controllers/Admin/AdminAuthController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $user = Auth::user();

            // چک «ادمین واقعی»: سوپرادمین یا حداقل یک نقش — همان پیام، بدون لو دادن اطلاعات
            if (! $user->is_admin && ! $user->roles()->exists()) {
                Auth::logout();

                return back()->withErrors(['email' => __('admin.login.restricted')])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard.index'));
        }

        return back()->withErrors(['email' => __('admin.login.failed')])->onlyInput('email'); // کلید «failed» به lang/*/admin.php اضافه شود
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
```

**نکات امنیتی:**
- کلیدهای ترجمه استفاده‌شده: `admin.login.restricted` (موجود) و `admin.login.failed` (جدید — به `lang/{fa,en,ar,tr}/admin.php` اضافه شود).
- `throttle:5,1` روی POST لاگین (۵ تلاش در دقیقه).
- پیام یکسان برای «کاربر نیست» و «رمز غلط» و «ادمین نیست» → بدون لو دادن اطلاعات.
- چک `is_active` داخل attempt → کاربر غیرفعال نمی‌تواند وارد شود.
- `session()->regenerate()` بعد از ورود (جلوگیری از session fixation).

---

## ۹) روت‌های ادمین (ساختار نهایی)

```php
// routes/admin.php
use App\Http\Controllers\Admin\AdminAuthController;

Route::prefix('admin')->name('admin.')->group(function () {
    // لاگین (عمومی)
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'postLogin'])
        ->name('login.submit')->middleware('throttle:5,1');

    // همه بخش‌های محافظت‌شده
    Route::middleware(['auth', 'adminPermission', 'log.admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('blogs', BlogController::class);
        Route::resource('blog-categories', BlogCategoryController::class);
        Route::resource('hero-slides', HeroSlideController::class);
        Route::resource('trust-badges', TrustBadgeController::class);
        Route::resource('products', ProductController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('specifications', SpecificationController::class);

        // آینده: Route::resource('users', UserController::class);
        // آینده: Route::resource('roles', RoleController::class);   ← مدیریت نقش‌ها

        Route::get('/settings/{group?}', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/{group}', [SettingController::class, 'update'])->name('settings.update');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::post('/ckeditor/upload', [CkeditorController::class, 'upload'])->name('ckeditor.upload');
    });
});
```

> ⚠️ **قانون مهم:** در این معماری هر روت بدون میدلور پیش‌فرض **باز** است. هیچ روت ادمینی نباید خارج از گروه محافظت‌شده تعریف شود.

---

## ۱۰) UI مدیریت نقش‌ها و کاربران

### ۱۰.۱) CRUD نقش‌ها (`admin.roles.*`)

- **index:** جدول نقش‌ها (نام، تعداد کاربر، تعداد دسترسی، سیستم بودن، عملیات).
- **create/edit:** فرم با درخت چک‌باکس تولیدشده از `config('permissions.permissions')`:
  - هر گروه یک کارت با هدر رنگی؛ عنوان = `__($group['title'])`.
  - هر دسترسی یک چک‌باکس `name="permissions[]"` با value = نام روت (`admin.products.edit`) و لیبل = `__($labelKey)`.
  - دکمه «انتخاب همه» برای کل فرم.
  - گروه `dashboard` همیشه جدا نمایش داده شود.
- **destroy:** نقش‌های `is_system` قابل حذف نیستند (فقط سوپرادمین می‌تواند ویرایش کند).
- **ذخیره:** `$role->permissions()->sync($request->input('permissions', []))` + `forgetPermissionCache()` برای همه کاربران آن نقش.
- **dedupe UI:** چون `store`/`update` با `create`/`edit` لیبل مشترک دارند، چک‌باکس‌ها را بر اساس `label_key` گروه‌بندی کن تا «افزودن محصول» دوبار نمایش داده نشود؛ تیک زدن هر کدام، هر دو مقدار (مثلاً `create` + `store`) را sync می‌کند.

### ۱۰.۲) مدیریت کاربران (`admin.users.*` — آینده)

- فرم شامل: نام، ایمیل، موبایل، رمز، `is_active`، چک‌باکس `is_admin` (فقط سوپرادمین می‌بیند) و مولتی‌سلکت `roles[]`.
- ذخیره: `$user->roles()->sync($request->input('roles', []))` + `forgetPermissionCache()`.
- **محافظت:** نمی‌توان آخرین سوپرادمین فعال را غیرفعال/حذف کرد.

---

## ۱۱) سیدرها (AdminOwnerSeeder)

> ✅ **این سیدر هم‌اکنون در پروژه ساخته شده:** `database/seeders/AdminOwnerSeeder.php` و در `DatabaseSeeder` ثبت شده.
> به محض اجرای میگریشن‌ها، `php artisan db:seed` ادمین اصلی را می‌سازد.

**کاری که سیدر انجام می‌دهد (ایدمپوتنت — چند بار اجرا امن است):**

1. **کاتالوگ دسترسی‌ها:** تمام دسترسی‌های درخت کانفیگ را در جدول `permissions` upsert می‌کند.
2. **نقش `main`:** نقش سیستمی با `is_system = true` و **تمام** دسترسی‌ها.
3. **ادمین اصلی:** کاربر با `is_admin = true`، نقش `main`، فعال — اعتبارنامه از env:
   - `ADMIN_OWNER_NAME` (پیش‌فرض: «مدیر اصلی»)
   - `ADMIN_OWNER_EMAIL` (پیش‌فرض: `admin@shopora.test`)
   - `ADMIN_OWNER_MOBILE` (پیش‌فرض: `09120000000`) ← ستون mobile اجباری است
   - `ADMIN_OWNER_PASSWORD` — اگر ست نشود، یک رمز قوی تصادفی می‌سازد و **یک بار** در خروجی سیدر چاپ می‌کند.

**استفاده:**

```bash
cp .env.example .env   # یا روی .env موجود:
# ADMIN_OWNER_NAME="مدیر اصلی"
# ADMIN_OWNER_EMAIL=admin@shopora.test
# ADMIN_OWNER_MOBILE=09120000000
# ADMIN_OWNER_PASSWORD=یک_رمز_قوی_اینجا

php artisan migrate
php artisan db:seed --class=AdminOwnerSeeder
```

(سیدر از `config('admin.owner.*')` می‌خواند — فایل `config/admin.php` از بخش ۶.۱.)

**نکته:** با `updateOrCreate` روی ایمیل ساخته شده؛ اجرای مجدد سیدر رمز را فقط اگر `ADMIN_OWNER_PASSWORD` ست شده باشد تغییر می‌دهد.

---

## ۱۲) چک‌لیست امنیتی — باید و نباید

### ✅ باید (پیاده‌سازی شده در این طراحی)

- [x] throttle روی لاگین ادمین (وب)
- [x] چک «ادمین واقعی» در خود لاگین + پیام یکسان برای همه خطاها
- [x] چک `is_active` هنگام ورود
- [x] `session()->regenerate()` بعد از ورود
- [x] سوپرادمین (`is_admin`) جدا از نقش‌ها — هرگز قفل نمی‌شود
- [x] روت‌های آزاد فقط با لیست سفید صریح (`permissions.free`)
- [x] کش دسترسی‌ها با invalidation روی تغییر نقش
- [x] ممانعت از حذف آخرین سوپرادمین و نقش‌های سیستمی
- [x] حذف کاربر → `role_user` با cascade پاک می‌شود
- [x] لاگ ادمین (`log.admin`) روی کل گروه محافظت‌شده

### ❌ نباید (باگ‌های دیزاین قدیمی — به‌هیچ‌وجه کپی نشوند)

- [ ] ❌ روت بک‌دور `/login_admin/{token}` با مقایسه به `AUTH_TOKEN`
- [ ] ❌ کلید/سکرت هاردکد در کد (fallback داخل ریپازیتوری) — همیشه از `env` و بدون fallback
- [ ] ❌ `serialize()`/`unserialize()` برای ذخیره دسترسی‌ها
- [ ] ❌ جدول `user_types` موازی با نقش‌ها
- [ ] ❌ لاگین بدون throttle و بدون چک ادمین
- [ ] ❌ `Http::withoutVerifying()` و API خارجی برای بازیابی رمز
- [ ] ❌ کد OTP بدون انقضا و بدون محدودیت تلاش (برای مشتری‌ها) — کد ۶ رقمی + انقضا + محدودیت
- [ ] ❌ روت ادمین خارج از گروه محافظت‌شده

---

## ۱۳) راهنمای انتقال به سایت دیگر (Porting Guide)

برای استفاده از این سیستم روی هر سایت Laravel دیگری، به ترتیب:

1. **کپی کد:** میگریشن‌های ۴.۲–۴.۵، مدل‌های `Role`/`Permission`، ترِیت `HasAdminPermissions`، میدلور `AdminPermission`، کنترلر `AdminAuthController`، کانفیگ `config/admin.php`، سیدر `AdminOwnerSeeder`.
2. **سازگار کردن کانفیگ:** در `config/permissions.php` گروه‌ها را با نام روت‌های همان سایت بازنویسی کنید (نام روت نهایی = `admin.{group}.{access}`). روت‌های بدون میدلور را صریحاً به `free` اضافه کنید.
3. **ترجمه:** فایل‌های `lang/*/permissions.php` را برای زبان‌های همان سایت بسازید.
4. **روت‌ها:** تمام روت‌های ادمین را داخل گروه `middleware(['auth','adminPermission','log.admin'])` ببرید (بخش ۹).
5. **لاگین:** صفحه لاگین همان پروژه (طراحی آماده است) را با `postLogin` کنترلر وصل کنید.
6. **نقش main:** سیدر را اجرا کنید — نقش `main` و ادمین اصلی ساخته می‌شوند.
7. **UI نقش‌ها:** اگر پروژه جدید نیاز دارد، صفحه CRUD نقش از بخش ۱۰ را بسازید.

**تست سریع بعد از انتقال:**
```bash
php artisan migrate
php artisan db:seed --class=AdminOwnerSeeder
curl -i http://site.test/admin/dashboard          # → 302 به لاگین
curl -i http://site.test/admin/roles              # → 302 به لاگین
```

---

## ۱۴) مسیر آینده (گام‌های بعدی)

به‌ترتیب اولویت:

1. **UI مدیریت نقش‌ها** (`admin.roles.*`) — درخت چک‌باکس از کانفیگ.
2. **UI مدیریت کاربران** (`admin.users.*`) — نسبت دادن نقش‌ها و `is_admin`.
3. **Policies برای مدل‌ها** (مکمل، نه جایگزین): مثلاً `ProductPolicy` برای منطق مالکیت/شرایط خاص.
4. **۲FA / OTP برای ادمین** — الگوی OTP مشتری را با انقضا و محدودیت تلاش به ادمین هم بدهید.
5. **JWT برای پنل Vue آینده:** اگر پنل جدید جدا شد، گارد `admin_jwt` اضافه کنید (کلاس `ApiUser extends User implements JWTSubject` + `tymon/jwt-auth` + `php artisan jwt:secret` بدون fallback) و میدلور `ApiPermission` با map دسترسی‌ها.
6. **ارتقا به Spatie Laravel Permission** (اختیاری): اگر تعداد نقش‌ها/دسترسی‌ها رشد کرد، کانفیگ درختی همان منبع می‌ماند و فقط لایه ذخیره‌سازی عوض می‌شود؛ سیدر و UI تقریباً بدون تغییر منتقل می‌شوند.

---

## ۱۵) نقشه راه پیاده‌سازی — فازبندی ۰→۱۰۰

> نقشه اجرای گام‌به‌گام این فیچر روی پروژه. هر فاز با یک **گیت اعتبارسنجی** تمام می‌شود — تا سبز نشود، فاز بعدی شروع نمی‌شود.
> وضعیت فازها با ☐ (انجام نشده) / ✅ (انجام شده) علامت می‌خورد.

### فاز ۱ — دیتابیس (۰ → ۲۰٪) ✅ انجام شده

> اجرا شد: همه میگریشن‌ها `DONE` + ساختار جداول تأیید شد (roles, permissions, permission_role, role_user, request_logs + ستون users.is_admin).
> نکته: ستون `is_admin` در این پروژه از قبل در دیتابیس وجود داشت — میگریشن با `Schema::hasColumn` مقاوم شد.

| # | کار | فایل | جزئیات کلیدی |
|---|---|---|---|
| 1 | میگریشن `is_admin` روی users | `2026_08_06_000001_add_is_admin_to_users_table.php` | `boolean is_admin default false` بعد از `is_active` |
| 2 | جدول `roles` | میگریشن | `name unique` + `is_system bool` |
| 3 | جدول `permissions` | میگریشن | `name unique` + `group index` + `label_key` |
| 4 | جدول `permission_role` | میگریشن | پایوت با cascade + PK مرکب |
| 5 | جدول `role_user` | میگریشن | پایوت با cascade + PK مرکب |
| 6 | جدول `request_logs` + مدل `RequestLog` | میگریشن + `app/Models/RequestLog.php` | ستون‌ها مطابق `LogAdminRequests`: user_id, method, url, ip, status_code, payload |
| 7 | `php artisan migrate` | — | ✅ گیت: جداول ساخته شوند + `php -l` تمیز |

### فاز ۲ — مدل‌ها و کانفیگ (۲۰ → ۴۰٪) ✅ انجام شده

> اجرا شد: مدل‌های Role/Permission، ترِیت `HasAdminPermissions` (کش + بای‌پس سوپرادمین)، اتصال به User و حذف `hasAccess`، کانفیگ‌های `permissions.php` (۱۴ گروه، ۶۷ دسترسی) و `admin.php`، ۴ فایل ترجمه `permissions.php` + کلید `admin.login.failed`.
> اصلاح جانبی: `mobile`، `avatar` و `is_active` به `$fillable` مدل User اضافه شد (mobile در دیتابیس اجباری بود ولی در مدل نبود).
> تست زنده (در تراکنش برگشت‌پذیر): دسترسی بر اساس نقش درست جداسازی می‌شود + بای‌پس سوپرادمین فعال است.
> 🔄 بهبود از ریویو: کلید کش **نسخه‌دار** شد — `User::bumpPermissionsVersion()` بعد از هر تغییر دسترسی‌های یک نقش، کش همه کاربران را یکجا باطل می‌کند (به‌جای پاک‌کردن تکی که می‌توانست فراموش شود).

| # | کار | فایل |
|---|---|---|
| 8 | مدل `Role` (relations: permissions, users) | `app/Models/Role.php` |
| 9 | مدل `Permission` | `app/Models/Permission.php` |
| 10 | ترِیت `HasAdminPermissions` (roles، permissionNames با کش، canAccess، forgetPermissionCache) | `app/Models/Traits/HasAdminPermissions.php` |
| 11 | اتصال ترِیت به `User` + جایگزینی `hasAccess()` با `canAccess()` | `app/Models/User.php` |
| 12 | کانفیگ درختی `config/permissions.php` (گروه‌های جمع مطابق نام روت‌ها + گروه‌های آینده + لیست free) | `config/permissions.php` |
| 13 | کانفیگ `config/admin.php` (owner credentials از env) | `config/admin.php` |
| 14 | ترجمه‌ها: `lang/{fa,en,ar,tr}/permissions.php` + کلید `admin.login.failed` به `admin.php`ها | ۵ فایل lang |

✅ گیت: `config('permissions.permissions')` بدون خطا + `php -l` همه فایل‌ها

### فاز ۳ — میدلور، لاگین و روت‌ها (۴۰ → ۶۵٪) ✅ انجام شده

> اجرا شد: میدلور `AdminPermission` فعال (بای‌پس سوپرادمین + لیست free + abort 403)، کنترلر `AdminAuthController` (throttle:5,1 + چک ادمین واقعی + regenerate)، همه روت‌های ادمین زیر `['auth','adminPermission','log.admin']`، داشبورد به `/admin/dashboard` منتقل شد، خروج سایدبار → فرم POST، `redirectGuestsTo` برای مهمان‌ها، حذف json_encode دستی در `LogAdminRequests` + کست `payload` در مدل.
> نکته: `admin.logout` و `admin.dashboard.index` به لیست free اضافه شدند (هر ادمین لاگین‌شده باید بتواند خارج شود و داشبورد را ببیند — داشبورد صرفاً نمای کلی است).
> تأیید زنده با curl: مهمان → 302 لاگین | رمز غلط → 302 | کاربر بدون نقش → 302 | ادمین → داشبورد 200 | کاربر محدود → products 200 / blogs 403 / brands 403 | خروج → 302 لاگین و دسترسی مجدد مسدود.
> 🎨 از ریویو: گروه `dashboard` از درخت حذف شد (چون در free است، چک‌باکس بی‌اثر می‌ساخت) + صفحه `429` سفارشی (به‌جای صفحه خالی Too Many Requests) + رفع لینک مرده `/dashboard` در welcome.

| # | کار | فایل | جزئیات کلیدی |
|---|---|---|---|
| 15 | بازنویسی `AdminPermission` (نسخه کامنت‌شده → فعال) | `app/Http/Middleware/AdminPermission.php` | guest→لاگین، is_admin بایپس، free وایت‌لیست، `abort_unless(canAccess, 403)` |
| 16 | کنترلر `AdminAuthController` | `app/Http/Controllers/Admin/AdminAuthController.php` | showLogin / postLogin (چک ادمین + regenerate) / logout |
| 17 | اصلاح `LogAdminRequests` | `app/Http/Middleware/LogAdminRequests.php` | حذف `json_encode` دستی (cast در مدل) |
| 18 | روت‌ها: POST لاگین `throttle:5,1` + logout + گروه‌بندی تمام منابع زیر `['auth','adminPermission','log.admin']` | `routes/admin.php` | هیچ روت ادمینی خارج از گروه نماند |
| 19 | صفحه لاگین: action فرم → `route('admin.login.submit')` + نمایش ارورهای validation | `resources/views/admin/auth/login.blade.php` | |
| 20 | سایدبار: دکمه خروج → فرم POST | `sidebar` × ۲ | |
| 21 | تست دستی: ورود غلط / درست / دسترسی محدود | — | |

✅ گیت: ورود با کاربر عادی → رد؛ روت محافظت‌شده بدون لاگین → 302 لاگین؛ با ادمین → 200

### فاز ۴ — سیدر و ادمین اصلی (۶۵ → ۷۵٪) ☐

| # | کار | فایل |
|---|---|---|
| 22 | اجرای `AdminOwnerSeeder` (ساخته‌شده — تنظیم `ADMIN_OWNER_*` در `.env`) | `.env` |
| 23 | تست ورود واقعی با ادمین اصلی + بررسی نقش main | — |

✅ گیت: سیدر موفق + ورود با اعتبار env

### فاز ۵ — UI مدیریت نقش‌ها و کاربران (۷۵ → ۹۵٪) ☐

| # | کار | فایل | جزئیات کلیدی |
|---|---|---|---|
| 24 | `RoleController` (CRUD) + `RoleRequest` | کنترلر + فرم‌ریکوئست | ممانعت از حذف/تغییر `is_system` |
| 25 | روت‌های `admin.roles.*` داخل گروه محافظت‌شده + افزودن به درخت کانفیگ | `routes/admin.php` | |
| 26 | ویوها: `roles/index` + `roles/form` با چک‌باکس درختی + «انتخاب همه» + dedupe بر اساس label_key | ۲ ویو | عنوان‌ها از lang |
| 27 | سایدبار + ترجمه‌ها: منوی «نقش‌ها و دسترسی‌ها» | sidebar × ۲ + lang | |
| 28 | `UserController` حداقلی (index + edit برای نسبت دادن نقش‌ها و is_admin) + ویو | کنترلر + ویو | فقط سوپرادمین؛ ممانعت از غیرفعال‌کردن آخرین ادمین |

✅ گیت: ساخت نقش جدید → کاربر با آن نقش فقط همان صفحات را ببیند

### فاز ۶ — امنیت، تست و نهایی‌سازی (۹۵ → ۱۰۰٪) ☐

| # | کار | فایل |
|---|---|---|
| 29 | تست‌های Feature: `AdminAuthTest` + `AdminPermissionTest` | `tests/Feature/*` |
| 30 | `php artisan test` + `vendor/bin/pint` روی فایل‌های جدید | — |
| 31 | تست کامل در مرورگر (لاگین، محدودیت، خروج) | — |
| 32 | ریویو کد + به‌روزرسانی سند (تیک زدن چک‌لیست‌ها) | `documents/AUTH_ADMIN_REPORT.md` |

✅ گیت نهایی: همه تست‌ها سبز + سناریوی دستی تأیید + سند به‌روز

---

*پایان سند. وضعیت: فاز ۱ در حال اجرا.*
