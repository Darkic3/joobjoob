# Notifications & SMS/Email Flows

> **Source of Truth:** `app/Services/NotificationService.php` , `app/Services/OtpService.php` , `app/Helpers/Sms.php` , `config/kavenegar.php` , `app/Models/Notification.php` . Hich `Notification` facade-e Laravel ( `Illuminate\Notifications` ) estefade nemishavad — notification-ha mostaghim dar `notifications` table neveshte mishavand. SMS faghat via `Sms` helper be Kavenegar. Email flow dar project **vojoud nadarad**.

## 1. Channel-ha — Che Vojoud Darad, Che Nadarad

| Channel | Mojoud? | Piyade Sazi | Queue? |
|---------|---------|-------------|--------|
| **Database** (`notifications` table) | ✅ Asli | `NotificationService::notifyUser/notifyAdmins` → `Notification::create()` | **SYNC EXECUTION** — dar haman request |
| **SMS** (Kavenegar) | ✅ Faghat OTP | `OtpService::send()` → `Sms::sendLookup(verify/lookup.json)` | **SYNC EXECUTION** — cURL mostaghim, timeout 30s |
| **Mail** | ❌ Hich estefade | `grep -rn "Mail::" app/ → 0` | — |
| **Broadcast / WebSocket** | ❌ | — | — |
| Laravel `Notifiable` / `Notification` classes | ❌ Hich `app/Notifications/*.php` | — | — |

## 2. `NotificationService` — Daghighan Che Tori Kar Mikonad

**File:** `app/Services/NotificationService.php` — hich `ShouldQueue` nist, hich `dispatch` nist.

### 2.1 `TYPES` Map — Single Source of Truth

```php
public const TYPES = [
  'order.created'      => ['icon'=>'📦','color'=>'accent'],
  'order.paid'         => ['icon'=>'✅','color'=>'success'],
  'order.shipped'      => ['icon'=>'🚚','color'=>'info'],
  'order.cancelled'    => ['icon'=>'❌','color'=>'danger'],
  'discount.welcome'   => ['icon'=>'🎁','color'=>'success'],
  'discount.expiring'  => ['icon'=>'⏰','color'=>'warning'],
  'order.new'          => ['icon'=>'📦','color'=>'accent'],   // admin
  'order.paid_admin'   => ['icon'=>'✅','color'=>'success'],  // admin
  'order.card_to_card' => ['icon'=>'💳','color'=>'warning'],  // admin
  'review.new'         => ['icon'=>'💬','color'=>'info'],     // admin
  'contact.new'        => ['icon'=>'✉️','color'=>'accent'],   // admin
];
// Fallback agar type naashena: ['icon'=>'🔔','color'=>'accent']
```

### 2.2 `notifyUser(User $user, string $type, string $title, ?string $body, ?string $link, ?array $data): Notification`

```
Step 1  $meta = TYPES[$type] ?? [icon=>🔔, color=>accent]
Step 2  Notification::create([
          notifiable_type => User::class,
          notifiable_id   => $user->id,
          type            => $type,          // string-e az TYPES (ya delkhah)
          title           => $title,         // e.g. 'سفارش شما ثبت شد'
          body            => $body,          // e.g. 'سفارش #12 به مبلغ 120,000 تومان ثبت شد.'
          icon            => $meta['icon'],  // auto az TYPES
          link            => $link,          // e.g. route('dashboard.order-detail', $id)
          data            => $data,          // array → cast json (e.g. [order_id=>12, amount=>120000])
          is_read         => false,
        ])
Step 3  return Notification
```

**SYNC EXECUTION** — dar haman DB transaction-e caller ejra mishavad (na dar queue). Caller-ha hame `try/catch Throwable → Log::error` darand → notification fail nemishkanad checkout ra.

### 2.3 `notifyAdmins(string $type, string $title, ?string $body, ?string $link, ?array $data): int`

```
Step 1  $admins = User::where('is_admin',true)->where('is_active',true)->get()
Step 2  loop har $admin → Notification::create(notifiable_type=User::class, notifiable_id=$admin->id, ...)
        $created++
Step 3  return $created  // tedad admin-haye notified
```

> N+1 notice: har admin yek `INSERT` jodagane — baraye tedad admin kam moshkeli nist. Agar admin ziad shod, `insert()` batch behtar ast.

### 2.4 Read/Unread API

| Method | Signature | Logic |
|--------|-----------|-------|
| `getUnreadCount(User $u)` | `int` | `Notification::getUnreadCount($u->id)` → `forUser($id)->unread()->count()` |
| `getAdminUnreadCount()` | `int` | `Notification::forAdmins()->unread()->count()` |
| `markAsRead(int $id, User $u)` | `bool` | `Notification::forUser($u->id)->find($id)?->markAsRead()` |
| `markAllAsRead(User $u)` | `int` | `Notification::markAllAsRead($u->id)` → `forUser->unread()->update(is_read=true)` |
| `getUserNotifications(User $u, $per=20)` | `LengthAwarePaginator` | `forUser($id)->latest()->paginate($per)` |
| `getAdminNotifications($per=20, ?$filter)` | `LengthAwarePaginator` | `forAdmins()->latest()` + `filter==='unread'→unread()` ya `filter!==all → ofType(filter)` |

`Notification` model scopes: `unread`, `forUser($userId)`, `forAdmins()` (whereIn admins), `ofType($type)`.

### 2.5 Koja-ha Notification Ferestade Mishavad — Map Kamel

| # | Dispatch Point (file:line) | Type | Girande | Title | Body (template) | Link |
|---|----------------------------|------|---------|-------|-----------------|------|
| 1 | `OrderService::create:108` | `order.created` | `order->user` | `سفارش شما ثبت شد` | `سفارش #{$id} به مبلغ {$paymentPrice} تومان ثبت شد.` | `dashboard.order-detail` |
| 2 | `OrderService::create:116` | `order.new` | hame admin-ha | `سفارش جدید ثبت شد` | `سفارش #{$id} از {$user->name} به مبلغ {$paymentPrice} تومان` | `admin.orders.show` |
| 3 | `OrderService::handleSuccess:203` | `order.paid` | `order->user` | `پرداخت سفارش تایید شد` | `پرداخت سفارش #{$id} به مبلغ {$paymentPrice} تومان با موفقیت انجام شد.` | `dashboard.order-detail` |
| 4 | `OrderService::handleSuccess:211` | `order.paid_admin` | hame admin-ha | `پرداخت سفارش تایید شد` | `پرداخت سفارش #{$id} از {$user->name} ... تایید شد.` | `admin.orders.show` |
| 5 | `OrderService::cancelOrder:250` | `order.cancelled` | `order->user` | `سفارش شما لغو شد` | `سفارش #{$id} لغو شد.` | `dashboard.order-detail` |
| 6 | `OtpService::send:105` | `discount.welcome` | `user` jadid | `کد تخفیف خوشامدگویی` | `تبریک! یک کد تخفیف ۱۰٪ برای اولین خرید شما ایجاد شد.` | `dashboard` |
| 7 | `PageController::sendContact:50` | `contact.new` | hame admin-ha | `پیام تماس جدید` | `پیام جدید از {$name} ({$email}) دریافت شد.` | `admin.contact.index` |
| 8 | `ProductController:361` | `review.new` | hame admin-ha | `نظر جدید ثبت شد` | `نظر جدید از {$reviewerName} برای محصول «{$productName}» ثبت شد.` | `admin.reviews.index` |
| 9 | `DashboardController::updateReceipt:68` | `order.card_to_card` | hame admin-ha | `فیش واریزی جدید دریافت شد` | `کاربر {$name} فیش سفارش #{$id} (کد پیگیری: {$trackingCode}) به مبلغ {$paymentPrice} تومان ارسال کرد.` | `admin.orders.show` |
| 10 | `OrderController::changeShippingStatus:92` | `order.shipped` | `order->user` | `وضعیت ارسال سفارش تغییر کرد` | `وضعیت ارسال سفارش #{$id} به «{$statusName}» تغییر یافت.` | `dashboard.order-detail` |

Hame `try/catch Throwable → Log::error` — notification hichgah flow-e asli ra nemishkanad.

## 3. SMS — Kavenegar Provider

### 3.1 Config

**File:** `config/kavenegar.php`

```php
'base_url'        => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1'),
'local_mobile'    => env('LOCAL_MOBILE'),          // dar local: lookup be in shomare miravad (agar khali → hich SMS ersal nemishavad)
'test_number'     => env('SMS_TEST_NUMBER'),        // dar local: send/sendarray be in shomare
'otp_ttl_minutes' => (int) env('OTP_TTL_MINUTES', 2),
```

`.env.example`:

```
KAVENEGAR_BASE_URL="https://api.kavenegar.com/v1"
LOCAL_MOBILE=
SMS_TEST_NUMBER="09123456789"
```

**API key va sender** az `settings` table (`setting('kavenegar_key')`, `setting('kavenegar_sender')`) khande mishavad, na az `.env` — dar `Sms::__construct()`.

### 3.2 `Sms` Helper — Method-ha

**File:** `app/Helpers/Sms.php`

| Method | Signature | HTTP | Timeout | Local Behavior |
|--------|-----------|------|---------|----------------|
| `sendLookup(string $template, array $tokens, string $mobile, string $type='sms')` | `string\|false\|RedirectResponse` | `GET verify/lookup.json?receptor={mobile}&type={type}&template={template}&{tokens_query}` | 30s / connect 15s, `SSL_VERIFYPEER=0` | agar `app()->environment('local')` → `mobile = config('kavenegar.local_mobile')`; agar `local_mobile` khali → `Log::info("skipped")` + `return false` (hich SMS nazan) |
| `sendSms(string $text, string $mobile)` | `array{success:bool, message?:string}` | `GET sms/send.json?receptor={receptor}&message={urlencode(text)}&sender={sender}` | 30s | `receptor = config('kavenegar.test_number')` |
| `sendSmsArray(array $messages)` | `array{success:bool, entries?:array}` | `POST sms/sendarray.json` ba `receptor/sender/message` be surat JSON array | 30s | har `receptor` → `test_number` |
| `sendSmsArrayTest(array $messages, ?$overrideSender)` | `array{success:bool}` | hamān POST | 30s | **hich** jaygozini — az `receptor` vaghei estefade mikonad |
| `execute(string $url)` | `string\|false\|RedirectResponse` | `GET $baseUrl/$token/$url` via cURL | 30s | agar `token` null → `Redirect::back()->with('error','لطفا با پشتیبانی تماس بگیرید')` |
| `executePost(string $url, ?array $postData)` | `string\|false` | `POST $baseUrl/$token/$url` form-urlencoded | 30s | agar `token` null → `Log::error` + `return false` |

### 3.3 OTP SMS — Flow-e Daghigh

**File:** `app/Services/OtpService.php:70-130`

```
Step 1  normalizeMobile(mobile) → 09xxxxxxxxx (tabdil argham farsi/arabi, +98/0098/9)
Step 2  validateMobile  → /^09\d{9}$/
Step 3  User::where(mobile)->first()
        agar jadid va name nadarad → return [success=>false, message=>otp_name_required]
        agar jadid → User::create(name,mobile,password=Str::random(32), is_active=true)
                     + WelcomeDiscountService::createForUser(user) → Discount WELCOME-XXXXXX
                     + notifyUser('discount.welcome')  [SYNC, try/catch]
Step 4  code = random_int(0,9999) 4 digits, user->update(confirm_code, confirm_code_expires_at=now+2min)
Step 5  template = setting('otp_template') ?: 'verify'
        siteName = cleanSiteName(setting('site_name'))  // agar "فروشگاه X" → "X"
        Sms::sendLookup(template, [token=>code, token2=>siteName], mobile)
Step 6  agar RedirectResponse → otp_sms_not_configured
        agar false → otp_sms_failed
        agar json return.status===200 → [success=>true, message=>otp_code_sent]
        vagar na → Log::error + [success=>false]
```

`verify(mobile, code)`: check `confirm_code===code` + `confirm_code_expires_at->isPast()` → `user->update(confirm_code=>null, expires=>null)` + `Auth::login(user, true)`.

## 4. Template-ha va Variable-haye Dynamic

| Template (Kavenegar) | Moteghayyer-ha (tokens) | Mesal-e Meghdar |
|----------------------|-------------------------|-----------------|
| `verify` (OTP, default) | `token` (code), `token2` (siteName) | `token=4821`, `token2=Joobjoob` |
| `otp_template` (custom, az settings) | haman `token`, `token2` | admin dar `settings` gharar midahad |

> Template-haye digar-e Kavenegar (masalan etela-e sefaresh) dar code estefade **nemishavand** — faghat OTP lookup estefade mishavad. Notification-haye sefaresh faghat database hastand, na SMS.

## 5. Email — Vojoud Nadarad

`grep -rn "Mail::\|Mailable\|ShouldQueue.*mail" app/ → 0` — hich email flow, hich `app/Mail/*.php`, hich `config/mail.php` dispatch dar `app/` nist.

## 6. Invariants baraye Vibe Coding

- **Notification = SYNC DB insert** — hich queue nist. Agar mikhahid SMS ya notification ra async konid, section 5-e `jobs-queues.md` ra bebinid.
- **SMS faghat OTP** — baraye sefaresh SMS ersal nemishavad. Agar mikhahid `order.paid` ra ham SMS konid, `Sms::sendLookup` ya `sendSms` ra ba template-e jadid-e Kavenegar seda bezanid — vali `try/catch` konid ta checkout nashkanad.
- **Local guard:** `sendLookup` dar `local` be `LOCAL_MOBILE` miravad ya hich — hargiz be mobile-e vaghei-e user dar dev ersal nemishavad. `sendSms`/`sendSmsArray` be `SMS_TEST_NUMBER` miravad.
- **API key az DB:** `Sms` token/sender ra az `settings` table mikhanad, na `.env`. Agar `kavenegar_key` khali bashad, `execute()` redirect ya `false` barmigardanad — OTP fail mishavad.
