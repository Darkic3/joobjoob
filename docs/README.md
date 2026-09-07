# Shopora Docs — Documentation Pack

> **هدف این پک:** داکیومنت‌های مستقل و قابل‌حمل برای هر بخش پروژه — دو کاربرد:
> 1) دادن به AI برای فهم منطق، دیباگ و کدزدن  2) بردن یک ماژول/فیچر به پروژه‌ی دیگر
>
> **قانون طلایی:** هر ادعا در این داکیومنت‌ها باید با `file:line` از کد واقعی پشتیبانی شود. اگر چیزی تأیید نشده، صریح `UNKNOWN` نوشته شده — نه حدس.

---

## ساختار

| پوشه | محتوا |
|---|---|
| `01-overview/` | معماری کلی، tech stack، قراردادهای کدنویسی، config map |
| `02-database/` | ERD، دیکشنری اسکیما، migrations، seeders |
| `03-modules/` | پک مستقل هر ماژول (۵ فایل، پایین را ببین) + `_shared/` گراف وابستگی سراسری |
| `04-workflows/` | فلوی چندماژوله: سفارش، پرداخت، سبد، تخفیف، inventory، اعلان‌ها، صف‌ها |
| `05-conventions/` | الگوهای تکرارشونده: Service/DTO، validation، traits، repository (نداریم)، enum |
| `06-integrations/` | سرویس‌های بیرونی: Kavenegar (SMS)، Chapar (حمل)، Storage |
| `07-security/` | RBAC، request logging، تهدیدها و آسیب‌پذیری‌های شناخته‌شده |
| `08-frontend/` | ساختار Blade، کامپوننت‌ها، JS، تم‌ها — معماری فرانت |
| `09-testing/` | نقشه‌ی تست‌ها: هر تست چه فیچری را پوشش می‌دهد |
| `10-ai-prompts/` | راهنمای استفاده از این پک با AI: global-context، conditional-context، قالب ماژول |
| `11-metrics/` | متریک‌های کدبیس |
| `_generated/` | خروجی ماشینی (routes-dump از `php artisan route:list`) — منبع حقیقت جدول‌های api-contract |

## پک هر ماژول (`03-modules/<module>/`)

| فایل | محتوا |
|---|---|
| `logic.md` | منطق کسب‌وکار گام‌به‌گام با evidence از کد |
| `api-contract.md` | جدول روت‌های واقعی (method/URI/name/middleware) |
| `dependencies.md` | گراف وابستگی: controller → service → model → بیرونی |
| `gotchas.md` | باگ‌های شناخته‌شده، تله‌ها، رفتار legacy |
| `porting.md` | چک‌لیست انتقال به پروژه‌ی دیگر: دقیقاً چه فایل‌هایی باید کپی/ساخته شود |

## وضعیت اسناد (Status Convention)

هر فایل بالای خود برچسب وضعیت دارد. **قبل از اعتماد، برچسب را چک کن:**

- ✅ **verified-trace** — از کد واقعی ردگیری شده با evidence. قابل اعتماد کامل.
- 🤖 **generated** — تولید ماشینی از منبع حقیقت (مثل routes-dump). برای روت‌ها دقیق است.
- ⚠️ **stub** — اسکلت حداقلی و صحیح ولی بدون trace عمیق. اگر فایل بدون برچسب است، با احتیاط بخوان.

| بخش | وضعیت |
|---|---|
| 01-overview (architecture, tech-stack, conventions) | ✅ |
| 02-database (erd, migrations, schema-dictionary) | ✅ |
| ماژول‌های deep-trace: products, payments, orders, cart, shipping | ✅ logic + ✅ dependencies |
| ماژول‌های بازسازی‌شده: audit, auth-otp, blog, contact, content, dashboard, discounts, inventory, localization, notifications, rbac-admin, reviews, search, settings, wishlist, users | ✅ (2026-09-06 بازنویسی از کد) |
| 04-workflows (همه ۱۶ فایل) | ✅ |
| 05-conventions, 06-integrations, 07-security | ✅ |
| 08-frontend, 09-testing | ✅ (اضافه‌شده 2026-09-06) |
| porting.md های ماژول‌ها | ✅ (2026-09-06) |
| `_generated/routes-dump.md` | 🤖 |

## استفاده با AI (خلاصه)

1. **همیشه** `10-ai-prompts/global-context.md` را اول بده (stack، gotchas حیاتی، naming).
2. بسته به کار، ماژول مربوطه را از `03-modules/` + workflow مربوطه از `04-workflows/` اضافه کن — جدول کامل در `10-ai-prompts/conditional-context.md`.
3. برای دیباگ: `gotchas.md` ماژول را حتماً بده — بیشتر باگ‌های معروف (واحد تومان/ریال، `name->fa`، cache flush، double restoreStock) آنجاست.
4. برای انتقال فیچر: `porting.md` ماژول را دنبال کن.
5. اگر داکیومنت با کد ناسازگار بود، **کد منبع حقیقت است** و داکیومنت را آپدیت کن.

## نگهداری

- بعد از هر تغییر روت‌ها: `php artisan route:list --json > docs/_generated/routes.json` و بازتولید `routes-dump.md`.
- هر ماژول جدید: قالب `10-ai-prompts/module-template.md` + یک فایل porting.md.
- برچسب وضعیت هر فایل را بعد از هر بازبینی آپدیت کن.
