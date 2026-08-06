# گزارش کامل سیستم احراز هویت و سطح دسترسی (Auth & RBAC) — پروژه common-template

> این گزارش حاصل بررسی کامل کد احراز هویت و مجوزدهی پروژه است: `config/auth.php`، `config/jwt.php`، میدلورهای `AdminPermission` / `ApiPermission` / `PanelPermission` / `MainMiddleware`، ماژول `User` (Entities, Controllers, Services, Migrations, Seeders, Config/site.php) و روتهای پنل و API.
> هدف: درک کامل دیزاین و انتقال آن به یک پروژه فروشگاهی جدید (غیرماژولار) همراه با اجتناب از اشکالات امنیتی موجود.

---

## ۱) نمای کلی معماری — دو دنیای جدا برای مدیریت

سیستم در این پروژه **سه جریان احراز هویت مستقل** دارد:

| جریان | مسیر | گارد | مکانیزم | مخاطب |
|---|---|---|---|---|
| پنل مدیریت (وب) | `/admin/*` | `web` (session) | ایمیل + رمز عبور → سشن | ادمینها |
| API مدیریت (پنل جدید) | `/api/admin/*` | `admin_jwt` (JWT) | ایمیل + رمز عبور → توکن JWT | پنل ادمین Vue / اپ |
| پنل مشتری (سایت) | `/panel/*` و `/checkout/*` | `web` (session) | **موبایل + کد OTP پیامکی** | کاربران سایت |

نکته کلیدی: **یک جدول واحد `users`** هم مشتریها و هم ادمینها را نگه میدارد. تمایز «ادمین بودن» نه با ستون جدا، بلکه با وجود یک ردیف در جدول `user_types` با `type = 'Admin'` انجام میشود.

```
┌─────────────────────────────┐
│          users              │  ← یک مدل برای همه (User / ApiUser)
├─────────────────────────────┤
│ id, full_name, mobile,      │
│ email, password, avatar,    │
│ confirm_code, birthday,     │
│ show_in_first_page,         │
│ deleted_at (SoftDeletes)    │
└─────────────────────────────┘
        │ 1                 │ 1
        │                   │
┌───────▼────────┐   ┌──────▼─────────────┐
│  role_user     │   │    user_types      │
│  (pivot)       │   ├────────────────────┤
│ user_id        │   │ user_id, type      │
│ role_id        │   │ type: Teacher|     │
└───────┬────────┘   │       Student|Admin│
        │            └────────────────────┘
┌───────▼────────┐
│     roles      │
├────────────────┤
│ name           │
│ permission     │ ← text: serialize() آرایه نام روتها
└────────────────┘
```

---

## ۲) پیکربندی گاردها (config/auth.php)

```php
'guards' => [
    'web' => [
        'driver'   => 'session',
        'provider' => 'users',
    ],
    'admin_jwt' => [
        'driver'   => 'jwt',
        'provider' => 'api_users',
    ],
],
'providers' => [
    'users'     => ['driver' => 'eloquent', 'model' => User::class],
    'api_users' => ['driver' => 'eloquent', 'model' => ApiUser::class],
],
```

- گارد پیشفرض `web` با درایور session — برای پنل وب و مشتریها.
- گارد `admin_jwt` با درایور JWT (پکیج **tymon/jwt-auth**) — برای API ادمین.
- **`ApiUser`** کلاس جداگانه نیست؛ فقط `User` را extends کرده و اینترفیس `JWTSubject` را پیاده میکند (`getJWTIdentifier` → id، `getJWTCustomClaims` → []). هر دو روی **جدول `users`** کار میکنند. (در پروژه مقصد میتوانید از همین الگو استفاده کنید: یک کلاس `ApiUser extends User implements JWTSubject`.)

### جزئیات JWT (config/jwt.php)
- الگوریتم: HS256 (کلید یکسان برای امضا/تأیید)
- `ttl`: 60×24 = **۱۴۴۰ دقیقه (۲۴ ساعت)**
- `refresh_ttl`: 20160 = **۱۴ روز**
- `blacklist_enabled`: true (توکنهای logout/refresh شده باطل میشوند)
- `required_claims`: iss, iat, exp, nbf, sub, jti
- `lock_subject`: true

---

## ۳) جریان ورود ادمین — وب (session)

**فایل:** `Modules/User/Http/Controllers/AuthController.php`

```php
public function postLogin(Request $request)
{
    $check = Auth::attempt([
        'email'      => $input['email'],
        'password'   => NumberHelper::persian2LatinDigit($input['password']),
        'deleted_at' => null,   // کاربران حذفشده (SoftDelete) نمیتوانند وارد شوند
    ]);
    if ($check) {
        return redirect('/admin')->with('success', 'خوش آمدید.');
    }
    return redirect('/admin/login')->with('error', 'ایمیل یا رمزعبور صحیح نیست.');
}
```

- **روت:** `POST /admin/post-login` (نام روت: بدون نام؛ `GET /admin/login` → `admin.login`)
- ورود صرفاً بر اساس **ایمیل + رمز** است. بررسی اینکه کاربر «ادمین» باشد در اینجا انجام **نمیشود** — فقط `Auth::attempt` روی جدول users. (یعنی هر کاربر معمولی هم اگر ایمیل/رمز داشته باشد میتواند سشن بگیرد؛ اما دسترسی به صفحهها توسط میدلور `AdminPermission` که «نقشها» را چک میکند، محدود میشود — در بخش ۵.)
- خروج: `Session::flush()` + `Auth::logout()` → ریدایرکت به `admin/login`
- **بازیابی رمز ادمین اصلی:** روت `GET /admin/change-password` با `throttle:3,1` — اولین کاربرِ دارای `UserType('Admin')` را پیدا میکند، یک رمز ۷ رقمی رندوم میسازد، `bcrypt` میکند و آن را **به شماره موبایلی که از API خارجی rahweb.com گرفته میشود پیامک میکند** (با `Http::withoutVerifying()` — بدون بررسی SSL!). اگر سایت منبع 404/500 بدهد، خطا برمیگرداند.

### روتهای وب ادمین (`Modules/User/Routes/web.php`)
```php
Route::prefix('admin')->group(function () {
    Route::get('/login', 'login')->name('admin.login');
    Route::get('/logout', 'logout')->name('admin.logout');
    Route::post('/post-login', 'postLogin');
    Route::get('/change-password', 'changePassword')
        ->middleware('throttle:3,1')->name('admin.change-password');
});
```
پنل ادمین (`/admin/...` برای ماژولها) با میدلور **`AdminPermission`** محافظت میشود (در فایل روت هر ماژول: `Route::middleware('AdminPermission')->prefix('admin')...`).

---

## ۴) جریان ورود ادمین — API (JWT)

**فایل:** `Modules/User/Http/Controllers/Api/AdminAuthController.php` + `Services/Api/AdminAuthService.php`

```php
public function login(array $credentials)
{
    if (! $token = auth('admin_jwt')->attempt($credentials)) {
        return ['error' => 'ایمیل یا رمز اشتباهه', 'status' => 401];
    }
    $user = auth('admin_jwt')->user();
    // شرط اضافه: کاربر باید حداقل یک نقش داشته باشد
    if (! $user->roles()->exists()) {
        return ['error' => 'حساب شما دسترسی لازم را ندارد', 'status' => 403];
    }
    return $this->respondWithToken($token);
}
```

- **روتها** (`Modules/User/Routes/api.php`):
  - `POST /api/admin/login` → name: `auth.login` (بدون محافظ — در map به `free`)
  - `GET /api/admin/me` → name: `auth.me` (محافظت: `auth:admin_jwt` + `api-permission`)
  - `POST /api/admin/logout` → `auth.logout`
  - `POST /api/admin/refresh` → `auth.refresh`
- پاسخ: `{ access_token, token_type: 'bearer', expires_in: ttl*60 }`
- **مهم:** شرط ورود موفق به API فقط داشتن حداقل یک نقش است — نه لزوماً UserType('Admin'). (در `ApiPermission` است که نوع Admin هم چک میشود.)

### نحوه محافظت روتهای API ادمین (نمونه Product/Routes/api.php)
```php
Route::prefix('admin/')->name("api.admin.")->middleware(['auth:admin_jwt', 'api-permission'])->group(function () {
    Route::prefix('product/')->controller(ApiProductController::class)->name('product.')->group(function () {
        Route::get('/', 'index')->name("index");
        Route::post('/add', 'store')->name("store");
        ...
    });
});
```
نام روت API = `api.admin.product.index` و `ApiPermission` با `str_replace('api.admin.', '', ...)` آن را به `product.index` تبدیل میکند تا در map پیدا کند.

---

## ۵) قلب سیستم: RBAC مبتنی بر «نام روت» (Route-Name Based Permissions)

این مهم‌ترین ایده دیزاین است که برای انتقال باید کاملاً درک شود:

### اصل طراحی
> **کلید دسترسی = نام روت** (مثلاً `admin.product.index`).
> در جدول `roles` یک ستون `permission` از نوع TEXT وجود دارد که یک **آرایه‌ی serialize شده از نام روت‌ها** را نگه می‌دارد.
> وقتی درخواستی به یک روت پنل می‌رسد، میدلور نام روت جاری را با اعضای این آرایه مقایسه می‌کند؛ اگر موجود بود → اجازه، وگرنه → رد.

```php
// Entities/Role.php — کل مدل!
class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'permission'];
}
```

### ساختار جدول roles
| ستون | نوع | توضیح |
|---|---|---|
| id | bigIncrements | |
| name | string nullable | نام نقش (مثل `main`، `content-manager`) |
| permission | text nullable | `serialize(['admin.dashboard', 'admin.product.index', ...])` |
| deleted_at | softDeletes | |

### دو موجودیت پشتیبان
```php
// User
public function roles() { return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id'); }
public function userTypes() { return $this->hasMany(UserType::class); }

// سینک کردن نوع‌های کاربر (Admin/Student/Teacher)
public function syncUserTypes($types)
{
    $this->userTypes()->whereNotIn('type', $types)->delete();
    foreach ($types as $row) {
        if (!$this->userTypes()->where('type', $row)->first()) {
            $this->userTypes()->create(['type' => $row]);
        }
    }
}
```

### اسکیمای جدول‌ها (برای کپی در یک مایگریشن واحد)
```php
// users
$table->id();
$table->string('full_name')->nullable();
$table->string('mobile')->nullable();
$table->string('email')->nullable();
$table->string('confirm_code')->nullable();   // کد OTP سایت
$table->text('password')->nullable();
$table->string('avatar')->nullable();
$table->integer('show_in_first_page')->nullable();
$table->integer('mobile_confirm')->nullable();
$table->string('birthday')->nullable();
$table->softDeletes();
$table->timestamps();

// roles
$table->bigIncrements('id');
$table->string('name')->nullable();
$table->text('permission')->nullable();
$table->softDeletes();
$table->timestamps();

// role_user (pivot)
$table->bigIncrements('id');
$table->integer('user_id')->nullable();
$table->integer('role_id')->nullable();

// user_types
$table->id();
$table->integer('user_id')->nullable();
$table->enum('type', ['Teacher', 'Student', 'Admin'])->nullable()->default('Student');
$table->timestamps();
```

### مدیریت موجودیت‌ها (UserService)
```php
public function create(UserDTO $userDto): void
{
    ...
    $user->roles()->attach($userDto->getRoles());
    $user->syncUserTypes($userDto->getUserTypes());
}
public function update(int $id, UserDTO $userDto): void
{
    ...
    $user->roles()->sync($userDto->getRoles());
    $user->syncUserTypes($userDto->getUserTypes());
}
```
- حذف کاربر: `UserObserver::deleted()` → `$item->roles()->detach()` (پاکسازی نقش‌ها)
- محافظت از حذف آخرین ادمین (در `UserController::destroy`): اگر فقط **یک** کاربر دارای `UserType('Admin')` باقی مانده باشد، حذف آن مجاز نیست.

---

## ۶) میدلورهای مجوزدهی — سه لایه

### ۶.۱ `AdminPermission` (وب — سشن) — میدلور اصلی پنل قدیمی
```php
class AdminPermission
{
    public function handle($request, Closure $next)
    {
        $segments = $request->segments();
        if ($this->auth->check()) {                              // سشن معتبر؟
            $admin = UserType::where('user_id', $this->auth->user()->id)
                             ->where('type', 'Admin')->first();  // آیا ادمین است؟
            if ($admin) {
                $roles = [];
                $user = User::find($this->auth->user()->id);
                foreach ($user->roles as $role) {                // همه نقش‌ها
                    $permission = unserialize($role->permission); // آرایه نام روت‌ها
                    if ($permission) $roles = array_merge($roles, $permission);
                }
                if ($segments[0] == config('site.admin')) {      // URL با /admin شروع شود
                    if (in_array(Route::currentRouteName(), $roles)) {
                        return $next($request);                  // ✅ اجازه
                    }
                }
            }
        }
        // ❌ رد: AJAX → 401 | وگرنه → ریدایرکت
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        } elseif (!str_contains('api', Route::currentRouteName())) {
            if (Auth::check() and $admin) {
                return redirect('/admin')->with('error', 'شما به این بخش دسترسی ندارید.');
            }
            return redirect('/admin/login')->with('error', 'شما به این بخش دسترسی ندارید.');
        }
    }
}
```
منطق به زبان ساده:
1. آیا لاگین است؟ (گارد پیش‌فرض = session `web`)
2. آیا ردیف `UserType('Admin')` دارد؟ (نه → رد)
3. همه نام روت‌های نقش‌هایش را جمع کن
4. اگر `Route::currentRouteName()` در آن مجموعه باشد → اجازه، وگرنه رد/ریدایرکت.

### ۶.۲ `ApiPermission` (API — JWT) — میدلور پنل جدید
```php
class ApiPermission
{
    public function handle($request, Closure $next)
    {
        $user = auth('admin_jwt')->user();
        if ($user) {
            $admin = UserType::where('user_id', $user->id)->where('type', 'Admin')->first();
            if ($admin) {
                $roles = [];
                foreach ($user->roles as $role) {
                    $permission = unserialize($role->permission);
                    if ($permission) $roles = array_merge($roles, $permission);
                }
                $api_list = \Config::get('site.api_permissions');      // map: روت API → کلید پنل
                $currentRoute = str_replace('api.admin.', '', Route::currentRouteName());
                $permissionRouteName = @$api_list[$currentRoute];
                if (in_array($permissionRouteName, $roles) || $permissionRouteName == 'free') {
                    return $next($request);
                }
            }
        }
        return response()->json(['error' => 'شما به این بخش دسترسی ندارید.'], 403);
    }
}
```
**تفاوت مهم با نسخه وب:** نام روت API (مثل `api.admin.product.index`) مستقیماً با نام روت پنل (مثل `admin.product.index`) یکی نیست؛ پس یک **جدول نگاشت** در کانفیگ وجود دارد:
```php
// config('site.api_permissions')
'product.index'  => 'admin.product.index',
'product.store'  => 'admin.product.create',
'product.update' => 'admin.product.edit',
'product.show'   => 'admin.product.index',
'auth.me'        => 'free',   // اندپوینت‌های آزاد
'auth.refresh'   => 'free',
'auth.logout'    => 'free',
```
اگر مقدار map شده `free` باشد → بدون بررسی اجازه داده می‌شود.

### ۶.۳ `PanelPermission` (سشن) — میدلور پنل مشتری
```php
// ثبت‌شده در Kernel با نام 'panel'
public function handle($request, Closure $next)
{
    if (!$this->auth->check()) {
        return redirect('/panel/login?url=' . $request->path());
    }
    return $next($request);
}
```
فقط «لاگین است؟» را چک می‌کند — **هیچ مجوز نقش‌محوری ندارد**. محافظ `/panel/*` و `/checkout/*` برای مشتری‌ها.

### ۶.۴ `MainMiddleware` (گلوبال)
به هر ریکوئست یک `session()->put('custom_data', $site_name . time())` اضافه می‌کند (کاربرد نامشخص/الگوریتمی). هیچ ربطی به auth ندارد.

---

## ۷) کانفیگ درایور مجوزها: `config('site')`

کانفیگ از فایل `Modules/User/Config/site.php` در `UserServiceProvider` با `mergeConfigFrom(..., 'site')` لود می‌شود:
```php
'admin' => 'admin',          // پیشوند URL پنل (استفاده در AdminPermission)
'panel' => 'panel',
```

### `site.permissions` — درخت دسترسی (۵۲ گروه)
این ساختار هم **موتور تولید UI چک‌باکس‌ها** است هم **منبع نقش full-access**:
```php
'permissions' => [
    'dashboard' => ['title' => 'داشبورد پنل'],
    'product' => [
        'title' => 'محصولات',
        'access' => [
            'index'  => 'مشاهده محصولات',
            'create' => 'اضافه کردن محصول',
            'edit'   => 'ویرایش محصول',
            'delete' => 'حذف محصول',
        ],
    ],
    'product-category' => [ ... ],
    'brand' => [ ... ],
    'order' => [ ... ],
    // ... مجموعاً ۵۲ گروه
],
```
نام کلید دسترسی نهایی = `admin.{key}.{accessKey}` → مثلاً `admin.product.create`.

### گروه‌های موجود (فهرست کلیدها)
`dashboard, admin, common, user, basket, setting, setting-partial, theme, comment, contact, permission, api, service, seo, redirect, canonical, blog, blog-category, gallery-category, gallery, worksample, fee, certificate, package, banner, highlight, branch, social, course, course-category, session, session-file, product-category, product, brand, tag, specification, specification-value, slogan, page, sitemap, product-video-faq, product-image, product-variant, product-property-spf-tag, order, bank, order-shipping-status, shipping-method, discount, state, city, address`

---

## ۸) UI مدیریت نقش‌ها و دسترسی‌ها (پنل)

**روت‌ها** (`Modules/User/Routes/web.php` — با میدلور `AdminPermission`):
| روت | متد کنترلر | کار |
|---|---|---|
| `GET /admin/permission` | `getPermission` | لیست نقش‌ها (paginate 20) |
| `GET /admin/permission/add` | `getAddPermission` | فرم ساخت نقش |
| `POST /admin/permission/add` | `postAddPermission` | ساخت نقش |
| `GET /admin/permission/edit/{id}` | `getEditPermission` | فرم ویرایش نقش |
| `POST /admin/permission/edit/{id}` | `postEditPermission` | ذخیره ویرایش |
| `GET /admin/permission/delete/{id}` | `getDeletePermission` | حذف نقش |

### ذخیره‌سازی (PermissionController)
```php
// ساخت نقش جدید:
$role->permission = serialize($request['access'] + ['fullAccess' => 0]);

// ویرایش:
$permissions = $request['access'];
array_push($permissions, "admin.dashboard");   // داشبورد همیشه اضافه می‌شود
$role->permission = serialize($permissions);
```

### فرم (Views/user/permission/form.blade.php)
- یک چک‌باکس «انتخاب همه» (`#select_all`) که همه چک‌باکس‌های فرم را toggle می‌کند.
- حلقه روی `Config::get('site.permissions')`؛ هر گروه یک کارت (widget) با هدر رنگی و چک‌باکس‌های `name="access[]"` با value = نام روت (`admin.product.index` و…).
- هنگام ویرایش، مقادیر ذخیره‌شده `unserialize` شده و چک‌باکس‌های متناظر `checked` می‌شوند.
- گروه `dashboard` به‌صورت جدا (و همیشه مجاز) نمایش داده می‌شود.

### مدیریت کاربران (UserController + UserService)
- `GET/POST /admin/user/create` و `edit` — فرم شامل: نام، ایمیل، موبایل، رمز، آواتار، چک‌باکس‌های `user_types[]` (Teacher/Student/Admin) و `roles[]`.
- اعتبارسنجی `AdminRequest`: در create، `user_types` **الزامی** است؛ موبایل ۱۱ رقمی و یکتا؛ ایمیل یکتا (با نادیده‌گرفتن soft-deleted).
- قابلیت `changePassword` برای هر کاربر توسط ادمین.
- خروجی اکسل کاربران (`UsersExport`).

---

## ۹) سیدرها و bootstrap اولیه

### `UserDatabaseSeeder` (راه‌اندازی اولیه)
```php
$full_access = ["admin.dashboard"];
foreach (Config::get('site.permissions') as $key => $permission) {
    foreach ($permission['access'] as $keyAccess => $access) {
        $full_access[] = 'admin.' . $key . '.' . $keyAccess;   // ساخت خودکار همه کلیدها
    }
}
DB::table('roles')->insert(['name' => 'main', 'permission' => serialize($full_access)]);

$user = User::create([
    'full_name' => "ادمین اصلی", 'email' => "admin@gmail.com",
    'password' => bcrypt('RahWeb2024'),
]);
$user->syncUserTypes(['Admin']);
$user->roles()->attach($role->id);   // نقش full-access
```
- نقش `main` (id=1) = **دسترسی کامل** — از روی کانفیگ به‌صورت خودکار تولید می‌شود، پس هر بار که یک گروه/اکشن به `site.permissions` اضافه شود، سیدر آن را هم پوشش می‌دهد.
- `RoleDatabaseSeeder` همان منطق را با `updateOrInsert(id=1)` انجام می‌دهد (به‌روزرسانی نقش main).

### نکته مهم برای انتقال
در پروژه مقصد باید «نقش full-access» را به‌عنوان یک مفهوم جدا داشته باشید (مثلاً ستون `is_super_admin` یا نام خاص) — وگرنه افزودن گروه دسترسی جدید مستلزم re-seed کردن نقش‌های موجود است.

---

## ۱۰) جریان احراز هویت مشتری (سایت) — برای کامل بودن تصویر

**کنترلر:** `app/Http/Controllers/Auth/AuthController.php` (سطح app)
```php
public function login(Request $request)
{
    $user = UserService::findByMobile($mobile);
    $code = rand(1000, 9999);
    if (!$user) {
        $user = UserService::createFromSite($request);   // ثبت‌نام خودکار با شماره
    }
    $user->update(['confirm_code' => $code]);
    $kavenegar = new Sms();
    $result = $kavenegar->sendLookup("verify", ["token" => $code], $user->mobile);
    return redirect(route("auth.mobile-code", ['mobile' => $mobile, 'url' => request()->get("url")]));
}

public function postCode()
{
    $user = UserService::findByMobileAndCode($mobile, $code);   // confirm_code = کد
    if ($user) {
        Auth::loginUsingId($user->id);
        BasketService::findUserBasketAndUpdateBaskets();        // ادغام سبد مهمان
        return redirect($url);
    }
    return back()->with("error", "کد نادرست است");
}
```
- ورود فقط با **موبایل + کد ۴ رقمی پیامکی** (Kavenegar، قالب `verify`). رمز عبور ندارد.
- کاربرِ ناشناس با همان شماره به‌صورت خودکار ساخته می‌شود (`confirm_code` در جدول users).
- کد در ستون `confirm_code` ذخیره می‌شود (بدون انقضا و بدون محدودیت تلاش! — نکته امنیتی در بخش ۱۱).
- بعد از ورود، سبد خرید مهمان به کاربر لاگین‌شده الحاق می‌شود.
- روت‌های آن: `GET/POST /panel/login` (throttle:3,1 روی صفحه)، `/panel/check-user-exists`، `/panel/mobile-code`، `/panel/confirm-code`، `/panel/logout`.

---

## ۱۱) اشکالات امنیتی و باگ‌ها — ⚠️ در کپی از آن‌ها اجتناب کنید

> در این بخش مواردی که در صورت کپی ناآگاهانه در پروژه جدید هم تکرار می‌شوند، شفاف علامت‌گذاری شده‌اند.

1. **🔴 بک‌دور لاگین:** روت `GET /login_admin/{token}` — اگر `token == env('AUTH_TOKEN')` باشد، مستقیم به‌عنوان اولین ادمین (`UserType('Admin')`) لاگین می‌کند. اگر `AUTH_TOKEN` لو برود یا ضعیف باشد، کل پنل در خطر است. این روت را در پروژه جدید نسازید.
2. **🔴 JWT_SECRET هاردکد شده در کد:** در `config/jwt.php`: `env('JWT_SECRET', 's08vhUIaEwsEdn5VVTYHXVNi1RMUWXEAUflRRSPlpvyDJfsAbhFCmix3jFVP40b2')` — یک fallback طولانی در ریپازیتوری. اگر `JWT_SECRET` در env نباشد، همه با کلید پیش‌فرض توکن جعل می‌کنند! حتماً در پروژه جدید از `php artisan jwt:secret` و بدون fallback استفاده کنید.
3. **🔴 بدون throttle روی لاگین ادمین:** `postLogin` وب و `AdminAuthService::login` (API) هیچ محدودیت تلاشی ندارند (فقط صفحه لاگین سایت `throttle:3,1` و change-password دارند). → Brute-force آزاد.
4. **🟠 روت‌های محافظت‌نشده پنل:** `POST /admin/ckeditor-upload` اصلاً میدلور ندارد؛ برخی روت‌ها `withoutMiddleware('AdminPermission')` دارند. در طراحی «نام روت» هر روتِ بدون میدلور به‌صورت پیش‌فرض باز است — باید لیست سفید/سیاه دقیق داشته باشید.
5. **🟠 باگ `str_contains` معکوس در AdminPermission:** `!str_contains('api', Route::currentRouteName())` — هاستک/نییدل جابه‌جا شده؛ یعنی عملاً برای همه روت‌ها true است و شاخه ریدایرکت اجرا می‌شود. (در عمل شاید خوش‌شانسانه جواب می‌دهد اما منطق درستی نیست.)
6. **🟠 متغیر نامشخص `$admin`:** در `AdminPermission` وقتی کاربر لاگین نیست، `$admin` در شاخه elseif تعریف‌نشده است → E_WARNING در PHP 8 و رفتار غیرقابل پیش‌بینی.
7. **🟠 کد OTP مشتری:** `confirm_code` بدون انقضا (expire) و بدون محدودیت تعداد تلاش ذخیره/بررسی می‌شود؛ کد ۴ رقمی → قابل حدس زدن در ۱۰۰۰۰ حالت.
8. **🟡 `fullAccess` اضافه می‌شود ولی هیچ‌جا چک نمی‌شود** (فقط در `postAddPermission`): یک کلید بی‌اثر.
9. **🟡 Dead import:** `AdminAuthService` از `App\Models\ApiUser` (که وجود ندارد) import می‌کند — در عمل استفاده نمی‌شود.
10. **🟡 بازیابی رمز ادمین با `Http::withoutVerifying()`** به API خارجی rahweb.com و ارسال رمز جدید با پیامک — وابستگی به سرویس خارجی + غیرفعال‌سازی بررسی SSL.
11. **🟡 لاگین وب ادمین نوع Admin را چک نمی‌کند** — فقط `Auth::attempt`؛ یک کاربر عادی با داشتن ایمیل/رمز (مثلاً کاربر سایت) سشن می‌گیرد و ریدایرکت به `/admin` می‌شود؛ فقط بعداً توسط `AdminPermission` (که UserType را چک می‌کند) رد می‌شود. نه‌تنها ناکارآمد، بلکه اطلاعات «وجود ایمیل/رمز» را لو می‌دهد.
12. **🟡 `changePassword` ادمین اصلی از راه دور:** اولین Admin را پیدا می‌کند و رمزش را عوض می‌کند بدون هیچ تأیید/۲FA.

---

## ۱۲) راهنمای انتقال به پروژه جدید (غیرماژولار)

### مرحله ۱ — اسکیمای دیتابیس (یک مایگریشن واحد)
```php
Schema::create('users', function (Blueprint $t) {
    $t->id();
    $t->string('full_name')->nullable();
    $t->string('mobile')->nullable();
    $t->string('email')->nullable()->unique();
    $t->string('password')->nullable();
    $t->string('confirm_code')->nullable();
    $t->string('avatar')->nullable();
    $t->softDeletes();
    $t->timestamps();
});
Schema::create('roles', function (Blueprint $t) {
    $t->id();
    $t->string('name');
    $t->text('permission')->nullable();   // serialize آرایه نام روت‌ها
    $t->softDeletes();
    $t->timestamps();
});
Schema::create('role_user', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
    $t->foreignId('role_id')->nullable()->constrained()->cascadeOnDelete();
});
Schema::create('user_types', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
    $t->enum('type', ['Admin', 'Editor', 'Customer'])->default('Customer');
    $t->timestamps();
});
```
(در پروژه مبدأ از FK واقعی استفاده نشده — اینجا پیشنهاد بهتر شده است.)

### مرحله ۲ — مدل‌ها
```php
// User (Authenticatable + SoftDeletes + Notifiable)
// + roles() belongsToMany + userTypes() hasMany + syncUserTypes()

// Role
protected $casts = ['permission' => 'array'];   // پیشنهاد: به‌جای serialize دستی

// ApiUser extends User implements JWTSubject   // برای گارد admin_jwt
```

### مرحله ۳ — کانفیگ guard ها (مثل config/auth.php مبدأ)
- گارد `web` → session + provider users
- گارد `admin_jwt` → jwt + provider api_users (مدل ApiUser)
- نصب `tymon/jwt-auth` و انتشار `config/jwt.php`؛ **حتمن** `php artisan jwt:secret` و حذف fallback هاردکد.

### مرحله ۴ — میدلورها (کپی تمیز شده)
- `AdminPermission` (وب) — نسخه اصلاح‌شده بدون باگ str_contains و بدون undefined `$admin`:
  ```php
  public function handle($request, Closure $next)
  {
      $user = $request->user();
      if (!$user || !$user->userTypes()->where('type', 'Admin')->exists()) {
          return $request->expectsJson()
              ? response()->json(['error' => 'Unauthorized'], 401)
              : redirect()->guest('/admin/login');
      }
      $permissions = $user->roles->flatMap(fn ($r) => $r->permission ?? []);
      abort_unless($permissions->contains(Route::currentRouteName()), 403);
      return $next($request);
  }
  ```
- `ApiPermission` (API) — با map `api_permissions` و مقدار `free`
- ثبت در Kernel: `'admin-permission'` و `'api-permission'`

### مرحله ۵ — کانفیگ site
```php
// config/site.php
'permissions' => [/* درخت گروه‌ها */],
'api_permissions' => [/* map روت API → کلید پنل */],
```

### مرحله ۶ — کنترلرها
- `AuthController` وب ادمین: login/logout/dashboard + (اختیاری) بازیابی رمز امن
- `AdminAuthService` API: login (با throttle + بررسی نقش) / me / logout / refresh
- `PermissionController`: CRUD نقش‌ها با فرم چک‌باکس درختی
- `UserController`: مدیریت کاربران + `UserObserver::deleted() → roles()->detach()` + محافظت آخرین ادمین

### مرحله ۷ — سیدر
```php
// نقش full-access 'main' + ادمین اصلی (با رمز قوی از env، نه هاردکد)
```

### پیشنهادهای بهبود (در نسخه کپی)
- 🔹 به‌جای serialize در ستون، جدول `permissions` + جدول `permission_role` واقعی (یا پکیج استاندارد Spatie Permission)
- 🔹 افزودن `rate-limit` روی همه اندپوینت‌های لاگین
- 🔹 ۲FA/OTP برای ورود ادمین (الگوی OTP موبایل سایت را می‌توانید به ادمین هم بدهید)
- 🔹 `email_verified_at` / تأیید ایمیل
- 🔹 ذخیره توکن‌های JWT در `blacklist` روی logout (قبلاً فعال است)

---

## ۱۳) چک‌لیست نهایی انتقال

- [ ] ساخت ۴ جدول: users / roles / role_user / user_types
- [ ] کلاس `ApiUser extends User implements JWTSubject` برای گارد JWT
- [ ] کانفیگ `web` + `admin_jwt` و `jwt:secret` بدون fallback هاردکد
- [ ] میدلور `AdminPermission` اصلاح‌شده (رفع باگ‌های str_contains و $admin)
- [ ] میدلور `ApiPermission` با map `api_permissions` (مقادیر `free`)
- [ ] میدلور `PanelPermission` برای پنل مشتری
- [ ] کانفیگ `site.permissions` (درخت UI + منبع نقش full-access) و `site.api_permissions`
- [ ] فرم CRUD نقش با چک‌باکس درختی + «انتخاب همه»
- [ ] فرم کاربران با `user_types[]` و `roles[]` + اعتبارسنجی یکتایی
- [ ] `UserObserver::deleted` → detach نقش‌ها + ممانعت از حذف آخرین ادمین
- [ ] سیدر نقش `main` (full-access) + ادمین اولیه (رمز از env)
- [ ] throttle روی لاگین ادمین (وب + API)
- [ ] ❌ نساختن روت بک‌دور `/login_admin/{token}`
- [ ] ❌ استفاده نکردن از `Http::withoutVerifying()` و API خارجی برای بازیابی رمز
- [ ] رفع نقص OTP مشتری: انقضا + محدودیت تلاش + (ترجیحاً) کد ۶ رقمی
