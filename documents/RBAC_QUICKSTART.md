# 🚀 راهنمای راه‌اندازی سریع RBAC پنل ادمین (Quickstart)

> این راهنما به شما می‌گوید چطور سیستم **احراز هویت + نقش و دسترسی (RBAC)** پنل ادمین را — که روی فروشگاه Shopora پیاده و تست شده — در **هر سایت Laravel 11 دیگری** راه‌اندازی کنید.
>
> 📖 طراحی کامل و جزئیات هر تصمیم: [`AUTH_ADMIN_REPORT.md`](./AUTH_ADMIN_REPORT.md)
>
> ⏱️ زمان تقریبی: **۱۵–۳۰ دقیقه** (بدون UI سفارشی)

---

## فهرست

1. [معماری در یک نگاه](#۱-معماری-در-یک-نگاه)
2. [فایل‌هایی که باید کپی کنید](#۲-فایلهایی-که-باید-کپی-کنید)
3. [گام ۱ — میگریشن‌ها](#گام-۱--میگریشنها)
4. [گام ۲ — مدل‌ها و ترِیت](#گام-۲--مدلها-و-تریت)
5. [گام ۳ — کانفیگ‌ها](#گام-۳--کانفیگها)
6. [گام ۴ — میدلورها و bootstrap](#گام-۴--میدلورها-و-bootstrap)
7. [گام ۵ — کنترلرها و روت‌ها](#گام-۵--کنترلرها-و-روتها)
8. [گام ۶ — ویوها](#گام-۶--ویوها)
9. [گام ۷ — ترجمه‌ها](#گام-۷--ترجمهها)
10. [گام ۸ — سیدر و اجرای نهایی](#گام-۸--سیدر-و-اجرای-نهایی)
11. [گام ۹ — تست‌ها](#گام-۹--تستها)
12. [تست سریع بعد از نصب](#تست-سریع-بعد-از-نصب)
13. [راهنمای عیب‌یابی](#۱۳-راهنمای-عیبیابی)
14. [چک‌لیست امنیتی](#۱۴-چکلیست-امنیتی)

---

## ۱) معماری در یک نگاه

```
users.is_admin (boolean)  ← سوپرادمین: بدون هیچ چکی از همه‌چیز عبور می‌کند
        │
        ▼  (role_user)
      roles ──(permission_role)──▶ permissions
                                     │
                          نام دسترسی = نام روت (admin.products.edit)
                                     │
                    منبع واحد حقیقت: config/permissions.php
```

- **کلید دسترسی = نام روت** (`admin.products.edit`) — ساده، خوانا، سازگار با `Route::resource`.
- **دو سطح مجوز:** سوپرادمین (`is_admin=1`) همیشه عبور می‌کند | بقیه: جمع دسترسی‌های همه نقش‌هایشان چک می‌شود.
- **کش نسخه‌دار:** دسترسی‌ها ۱ ساعت کش می‌شوند؛ با `User::bumpPermissionsVersion()` بعد از هر تغییر نقش، کش **همه** کاربران یکجا باطل می‌شود.
- **روت‌های آزاد** (لاگین، خروج، داشبورد، آپلود CKEditor) فقط با لیست سفید صریح در `config/permissions.php → free`.

---

## ۲) فایل‌هایی که باید کپی کنید

> این فایل‌ها را از پروژه Shopora به پروژه جدید منتقل کنید (مسیرها نسبت به ریشه پروژه):

### 📦 میگریشن‌ها (۶ فایل)
| فایل | کار |
|---|---|
| `database/migrations/2026_08_06_000001_add_is_admin_to_users_table.php` | ستون `is_admin` روی users |
| `database/migrations/2026_08_06_000002_create_roles_table.php` | جدول roles |
| `database/migrations/2026_08_06_000003_create_permissions_table.php` | جدول permissions |
| `database/migrations/2026_08_06_000004_create_permission_role_table.php` | پایوت |
| `database/migrations/2026_08_06_000005_create_role_user_table.php` | پایوت |
| `database/migrations/2026_08_06_000006_create_request_logs_table.php` | لاگ درخواست‌های ادمین |

### 🧠 مدل‌ها و ترِیت (۴ فایل)
| فایل | کار |
|---|---|
| `app/Models/Role.php` | نقش (روابط permissions, users) |
| `app/Models/Permission.php` | دسترسی |
| `app/Models/RequestLog.php` | لاگ (کست payload → array) |
| `app/Models/Traits/HasAdminPermissions.php` | ترِیت: `roles()`, `permissionNames()` (کش نسخه‌دار), `canAccess()`, `bumpPermissionsVersion()` |

### ⚙️ کانفیگ (۲ فایل)
| فایل | کار |
|---|---|
| `config/permissions.php` | درخت دسترسی‌ها + لیست free |
| `config/admin.php` | اعتبارنامه ادمین اصلی از env |

### 🛡️ میدلور (۲ فایل)
| فایل | کار |
|---|---|
| `app/Http/Middleware/AdminPermission.php` | محافظ RBAC (لاگین → بایپس → free → 403) |
| `app/Http/Middleware/LogAdminRequests.php` | لاگ‌کردن درخواست‌های ادمین |

### 🎮 کنترلرها و فرم‌ریکوئست (۵ فایل)
| فایل | کار |
|---|---|
| `app/Http/Controllers/Admin/AdminAuthController.php` | لاگین/خروج |
| `app/Http/Controllers/Admin/RoleController.php` | CRUD نقش‌ها |
| `app/Http/Controllers/Admin/UserController.php` | مدیریت کاربران |
| `app/Http/Requests/Admin/RoleRequest.php` | اعتبارسنجی نقش |
| `app/Http/Requests/Admin/UserRequest.php` | اعتبارسنجی کاربر |

### 🎨 ویوها (۷ فایل + ویرایش)
| فایل | کار |
|---|---|
| `resources/views/admin/auth/login.blade.php` | صفحه لاگین — **الگوی مرجع**: برندینگ و کلاس‌های CSS مخصوص Shopora را با پروژه خودتان تطبیق دهید (فرم باید به `route('admin.login.submit')` پست کند) |
| `resources/views/admin/roles/{index,create,edit,_form}.blade.php` | CRUD نقش با چک‌باکس درختی |
| `resources/views/admin/users/{index,create,edit}.blade.php` | مدیریت کاربران (لیست / افزودن / ویرایش) |
| `resources/views/components/admin/sidebar.blade.php` + `mobile-sidebar.blade.php` | ⚠️ **فقط الگو** — منوی «نقش‌ها» و «کاربران» را بردارید و با روت‌های پروژه خودتان تطبیق دهید (سایدبار Shopora به روت‌هایی مثل hero-slides ارجاع می‌دهد که در پروژه جدید وجود ندارند) |

### 🌐 ترجمه‌ها (۸ فایل)
| فایل | کار |
|---|---|
| `resources/lang/{fa,en,ar,tr}/permissions.php` | عنوان دسترسی‌ها (۴ زبان) |
| `resources/lang/{fa,en,ar,tr}/admin.php` | ویرایش: کلیدهای `sidebar.roles`, `sidebar.users`, `login.failed`, `login.restricted` |

### 🧪 تست‌ها (۳ فایل + phpunit.xml)
| فایل | کار |
|---|---|
| `tests/Feature/AdminAuthTest.php` | لاگین |
| `tests/Feature/AdminPermissionTest.php` | مجوزها |
| `tests/Feature/RoleCrudTest.php` | CRUD نقش‌ها |
| `phpunit.xml` | تنظیم دیتابیس تست جداگانه |

### 📝 ویرایش‌های لازم در فایل‌های موجود
| فایل | تغییر |
|---|---|
| `app/Models/User.php` | `use HasAdminPermissions;` + `is_admin`/`is_active` به fillable و casts |
| `bootstrap/app.php` | alias میدلورها + `redirectGuestsTo` |
| `routes/admin.php` | بازنویسی کامل (بخش ۵) |
| `database/seeders/DatabaseSeeder.php` | ثبت `AdminOwnerSeeder` |

---

## گام ۱ — میگریشن‌ها

۶ فایل میگریشن را کپی کنید و اجرا کنید:

```bash
php artisan migrate
```

> 💡 اگر پروژه شما **از قبل** ستون `is_admin` روی users دارد، میگریشن ۱ با گارد `Schema::hasColumn` از خطا جلوگیری می‌کند.

---

## گام ۲ — مدل‌ها و ترِیت

مدل‌های `Role`، `Permission`، `RequestLog` و ترِیت `HasAdminPermissions` را کپی کنید، سپس `User` را ویرایش کنید:

```php
// app/Models/User.php
use App\Models\Traits\HasAdminPermissions;

class User extends Authenticatable
{
    use HasFactory, HasAdminPermissions, Notifiable;

    protected $fillable = ['name', 'mobile', 'email', 'password', 'avatar', 'is_active', 'locale', 'theme', 'is_admin'];

    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'is_admin'   => 'boolean',
            'is_active'  => 'boolean',
        ];
    }
}
```

> ⚠️ اگر ستون `mobile` در دیتابیس شما `NOT NULL` است، حتماً به `$fillable` اضافه کنید (باگ رایج: ثبت‌نام بدون mobile کرش می‌کند).

---

## گام ۳ — کانفیگ‌ها

### ۳.۱) `config/permissions.php` — منبع واحد حقیقت

**مهم‌ترین بخش.** گروه‌ها را با نام روت‌های **همان سایت** هماهنگ کنید:

```php
return [
    // روت‌هایی که فقط «احراز هویت» می‌خواهند (خروج، داشبورد، آپلود CKEditor...)
    'free' => [
        'admin.ckeditor.upload',
        'admin.logout',
        'admin.dashboard.index',
    ],

    'permissions' => [
        // ⚠️ کلید گروه = بخش نام روت واقعی (جمع، مثل Route::resource)
        'products' => [
            'title'  => 'permissions.groups.products',
            'access' => [
                'index'   => 'permissions.access.products.index',
                'create'  => 'permissions.access.products.create',
                'store'   => 'permissions.access.products.create', // لیبل مشترک → UI dedupe می‌کند
                'edit'    => 'permissions.access.products.edit',
                'update'  => 'permissions.access.products.edit',
                'destroy' => 'permissions.access.products.delete',
            ],
        ],
        // ... بقیه گروه‌ها (blogs, brands, roles, users, settings, ...)
    ],
];
```

**قوانین طلایی:**
- نام روت نهایی = `admin.{group}.{access}` — پس کلید گروه باید دقیقاً بخش نام روت باشد.
- هر روت ادمینی که خارج از این الگوست، یا به `free` اضافه کنید یا گروه مخصوصش را بسازید.
- افزودن گروه جدید = فقط یک بلوک در کانفیگ + یک بلاک در فایل‌های ترجمه.

### ۳.۲) `config/admin.php` — اعتبارنامه ادمین اصلی

```php
return [
    'owner' => [
        'name'     => env('ADMIN_OWNER_NAME', 'مدیر اصلی'),
        'email'    => env('ADMIN_OWNER_EMAIL', 'admin@example.test'),
        'mobile'   => env('ADMIN_OWNER_MOBILE', '09120000000'),
        'password' => env('ADMIN_OWNER_PASSWORD'), // null → رمز تصادفی یک‌باره
    ],
];
```

---

## گام ۴ — میدلورها و bootstrap

### ۴.۱) `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'adminPermission' => \App\Http\Middleware\AdminPermission::class,
        'log.admin'       => \App\Http\Middleware\LogAdminRequests::class,
    ]);

    // مهمان‌هایی که به روت‌های محافظت‌شده می‌روند → لاگین ادمین
    $middleware->redirectGuestsTo(function (Request $request) {
        return str_starts_with($request->path(), 'admin')
            ? route('admin.login')
            : url('/');
    });
})
```

### ۴.۲) میدلور `AdminPermission` — منطق محافظ

```
۱) لاگین نیست        → 302 به لاگین (یا 401 برای AJAX)
۲) is_admin          → عبور بدون چک (هرگز قفل نمی‌شود)
۳) روت در لیست free  → عبور
۴) نام روت در دسترسی‌های نقش‌های کاربر → عبور | در غیر این صورت → 403
```

---

## گام ۵ — کنترلرها و روت‌ها

### ۵.۱) `routes/admin.php` — ساختار نهایی

```php
Route::prefix('admin')->name('admin.')->group(function () {
    // لاگین (عمومی)
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'postLogin'])
        ->name('login.submit')->middleware('throttle:5,1');

    // همه بخش‌های محافظت‌شده — ⚠️ هیچ روت ادمینی خارج از این گروه!
    Route::middleware(['auth', 'adminPermission', 'log.admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('roles', RoleController::class)->except('show'); // show = تله 403
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);

        // بقیه منابع سایت شما زیر همین گروه:
        // Route::resource('products', ProductController::class);
        // ...

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });
});
```

> ⚠️ **قانون مهم:** در این معماری هر روت بدون میدلور پیش‌فرض **باز** است. همه روت‌های ادمین باید داخل گروه محافظت‌شده باشند.

### ۵.۲) نکات مهم کنترلرها

- **`RoleController::store/update`** — چک‌باکس‌ها بر اساس `label_key` dedupe می‌شوند (`create`+`store` با هم toggle). `expandedPermissionIds()` نام‌های هم‌لیبل را بسط می‌دهد.
- **نقش سیستمی `main`:** ویرایش فقط سوپرادمین، حذف همیشه 403.
- **`UserController::update`** — محافظت دوتایی: غیر سوپرادمین نمی‌تواند سوپرادمین را ویرایش کند (403) + **آخرین سوپرادمین فعال را نمی‌توان غیرفعال/تنزل داد**.
- بعد از هر تغییر نقش/دسترسی: `User::bumpPermissionsVersion()`.

---

## گام ۶ — ویوها

- صفحه لاگین (`admin/auth/login.blade.php`) — **الگوی مرجع**: برندینگ را با پروژه خودتان عوض کنید؛ فرم باید به `route('admin.login.submit')` پست کند + `@csrf`.
- CRUD نقش‌ها — چک‌باکس درختی از `config('permissions.permissions')`، با «انتخاب همه» و dedupe.
- ⚠️ **سایدبار را کپی نکنید** — فقط منوی «نقش‌ها» و «کاربران» را الگوبرداری کنید؛ سایدبار Shopora به روت‌های پروژه خودش ارجاع می‌دهد و در پروژه جدید «Route not defined» می‌دهد.
- دکمه خروج در سایدبار = **فرم POST با `@csrf`** (نه لینک).

---

## گام ۷ — ترجمه‌ها

- `lang/*/permissions.php` — کلیدهای گروه/دسترسی برای هر زبان سایت.
- `lang/*/admin.php` — کلیدهای لاگین و سایدبار (مطابق پروژه شما).

---

## گام ۸ — سیدر و اجرای نهایی

### ۸.۱) ثبت در `DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        \Database\Seeders\AdminOwnerSeeder::class, // اولین — ادمین اصلی
        // ... سیدرهای دیگر
    ]);
}
```

### ۸.۲) تنظیم `.env`

```dotenv
ADMIN_OWNER_NAME="مدیر اصلی"
ADMIN_OWNER_EMAIL=admin@example.test
ADMIN_OWNER_MOBILE=09120000000
ADMIN_OWNER_PASSWORD=یک_رمز_قوی_اینجا   # خالی بگذارید → رمز تصادفی یک‌باره
```

### ۸.۳) اجرا

```bash
php artisan migrate
php artisan db:seed --class=AdminOwnerSeeder
```

سیدر (ایدمپوتنت — چند بار اجرا امن است):
1. کاتالوگ دسترسی‌ها را از کانفیگ upsert می‌کند
2. نقش سیستمی `main` (غیرقابل حذف) با تمام دسترسی‌ها
3. ادمین اصلی با `is_admin=true` + نقش `main`

---

## گام ۹ — تست‌ها

دیتابیس تست جداگانه بسازید (تا دیتای واقعی هرگز لمس نشود):

```bash
mysql -e "CREATE DATABASE shopora_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

`phpunit.xml` را با **محیط خودتان** هماهنگ کنید — فایل کپی‌شده اعتبارنامه‌های محلی Shopora را هاردکد دارد (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_USERNAME=root`, `DB_PASSWORD=` و `DB_DATABASE=shopora_test`). اگر اعتبارنامه MySQL شما متفاوت است، حتماً آن‌ها را تغییر دهید وگرنه تست‌ها در مرحله migrate با خطای اتصال شکست می‌خورند. سپس:

```bash
php artisan test --testsuite=Feature
```

> ⚠️ `RefreshDatabase` دیتابیس را از نو می‌سازد — **هرگز** به دیتابیس واقعی وصل نکنید.

---

## تست سریع بعد از نصب

```bash
# ۱) بدون لاگین → باید 302 به لاگین بدهد
curl -i http://site.test/admin/dashboard

# ۲) صفحه لاگین باید 200 رندر شود
curl -i http://site.test/admin/login

# ۳) ورود واقعی با POST (توکن CSRF را از صفحه لاگین بردارید)
TOKEN=$(curl -s -c cookies.txt http://site.test/admin/login \
  | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)
curl -s -b cookies.txt -c cookies.txt -o /dev/null -w "login: %{http_code}\n" \
  -d "_token=$TOKEN&email=admin@example.test&password=رمز_شما" \
  http://site.test/admin/login

# ۴) با سشن لاگین‌شده → باید 200 بدهد
curl -s -b cookies.txt -o /dev/null -w "roles: %{http_code}\n" http://site.test/admin/roles
curl -s -b cookies.txt -o /dev/null -w "users: %{http_code}\n" http://site.test/admin/users
```

> یا به سادگی در مرورگر لاگین کنید و صفحات را چک کنید — تست کامل تعاملی در بخش گام ۹.

**سناریوی تست کامل:**
| سناریو | نتیجه مطلوب |
|---|---|
| مهمان → هر صفحه ادمین | 302 به لاگین |
| کاربر عادی (بدون نقش) → ورود | رد + پیام یکسان |
| کاربر غیرفعال → ورود | رد |
| کاربر با نقش «فقط محصولات» → /admin/products | 200 |
| همان کاربر → /admin/blogs | 403 |
| غیرفعال‌کردن آخرین سوپرادمین | رد + پیام خطا |
| ۶ تلاش لاگین اشتباه در ۱ دقیقه | 429 |

---

## ۱۳) راهنمای عیب‌یابی

| مشکل | علت و راه‌حل |
|---|---|
| «کلمه ناشناخته / 403 برای همه» | نقش‌ها روی کاربر نیست یا درخت کانفیگ با نام روت‌ها جور نیست → نام روت واقعی را با `php artisan route:list` چک کنید |
| «حذف نقش سیستمی 403 می‌دهد» | درست است! `main` با `is_system=1` هرگز حذف نمی‌شود |
| لاگین همیشه خطا می‌دهد ولی رمز درست است | کاربر نقش ندارد و سوپرادمین نیست → به او نقش بدهید یا `is_admin=1` |
| `env()` در سیدر null است | سیدر از `config('admin.owner.*')` می‌خواند — بعد از `php artisan config:cache` دوباره `config:clear` کنید |
| روت `show` → 403 برای نقش main | `Route::resource(...)->except('show')` را ببینید — show در کاتالوگ نیست (عمداً) |
| تغییر دسترسی نقش دیده نمی‌شود | `User::bumpPermissionsVersion()` را بعد از sync صدا بزنید (کش نسخه‌دار) |

---

## ۱۴) چک‌لیست امنیتی

**باید:**
- [x] throttle روی لاگین ادمین (`throttle:5,1`)
- [x] چک «ادمین واقعی» در لاگین + پیام یکسان برای همه خطاها
- [x] چک `is_active` هنگام ورود
- [x] `session()->regenerate()` بعد از ورود
- [x] سوپرادمین جدا از نقش‌ها — هرگز قفل نمی‌شود
- [x] روت‌های آزاد فقط با لیست سفید صریح
- [x] کش با invalidation سراسری (`bumpPermissionsVersion`)
- [x] ممانعت از حذف نقش سیستمی + آخرین سوپرادمین
- [x] غیر سوپرادمین نمی‌تواند سوپرادمین را ویرایش کند (GET و PUT)

**نباید:**
- [ ] روت بک‌دور `/login_admin/{token}` ❌
- [ ] کلید/سکرت هاردکد در کد ❌
- [ ] `serialize()` برای ذخیره دسترسی‌ها ❌
- [ ] جدول `user_types` موازی با نقش‌ها ❌
- [ ] روت ادمین خارج از گروه محافظت‌شده ❌
- [ ] `Http::withoutVerifying()` و API خارجی برای بازیابی رمز ❌

---

*برای جزئیات کامل معماری، مقایسه با طراحی‌های قدیمی و مسیر آینده (Policies، 2FA، JWT، Spatie) به [`AUTH_ADMIN_REPORT.md`](./AUTH_ADMIN_REPORT.md) مراجعه کنید.*
