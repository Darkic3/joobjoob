# Contact — Gotchas & Known Traps

> **Status:** ✅ verified-trace — بازنویسی از کد واقعی (2026-09-06)
> Hameye madah-ha az code tasdiq shode-and; har item `file:line` darad.

## 1. `department` free-text ast — validation-e `in:` nadarad

`PageController.php:42` faghat `['nullable','string','max:40']` — hich checki baraye boodan dar list-e `sales/support/technical/other` (lang fa `:288-292`) nist. Har string-e ta 40 char (misln "MyCompany") dar DB zakhire mishavad.

**Natije-ye domino** dar Blade-e admin:

```php
{{ __('messages.pages.contact.departments.' . $contact->department, [], $contact->department) }}  // list.blade.php:89, show.blade.php:66
```

Argome sevom-e `__()` **locale** ast, na fallback value! Baraye department-e namaloom: locale-e `MyCompany` vojood nadarad → translator be fallback locale mire → line anja nist → **kelid-e khorooji por chap mishavad** (`messages.pages.contact.departments.MyCompany`). Baraye key-haye motalleq (sales/...) alate tahitii kar mikone chon locale-e invalid az dast miravad va fallback locale (fa) peyda mikonash. Agar department-e custom port mikonid, ya `in:` ezaf konid ya label-e amn jaygozin konid.

## 2. `markAsRead` dar GET show — side-effect ruye action-e «read-only»

`Admin/ContactController.php:34` — baz kardan-e safhe-ye detail peyam ra be `read_at` mohar mikonad. Prefetch/crawler-e admin panel (misln preview-e link) peyam-ha ra nakhunde «khande-shode» mikonad. Idempotent ast (`ContactRequest.php:33` check-e null) vali ghabel-e bazgardandan nist.

## 3. Hard delete — SoftDeletes nadarad

`ContactRequest.php:8` extends Model-e khales; `destroy()` (`Admin/ContactController.php:41`) radif-ra bedoon bazgasht pak mikonad. Hazf = bazgasht-napazir (bar khalaf-e aksar-e module-ha-ye Joobjoob).

## 4. Search-e admin ruye phone nemigardad

`Admin/ContactController.php:19-21` faghat `name/email/message`. Karbari ke shomare-e 0912... ro dar search bezanad, peyam-i ke phone hamin bood ra nemiyabad.

## 5. Filter-e `status` faghat ba `?status=1` faal mishavad

`Admin/ContactController.php:24` — `$request->boolean('status') === true`. Checkbox-e form value=1 darad (`list.blade.php:25-27`) pas az UI khob kar mikonad; vali `?status=0` ya `?status=` «hame» neshan midahad (na «faghat khande-shode»). Agar keh expect mikoni `status=0` = read-only filter, injuri nist.

## 6. Notification-e admin haqq-e `is_admin` mikhahad — role-dar-haa nemigirand

`NotificationService.php:68` — `User::where('is_admin', true)->where('is_active', true)`. Admin-i ke faghat ba role/permission-e `admin.contact.index` dastresi darad (superadmin nist), **notification nemigirad**. Daryafte notification va dastresi-e inbox sync nistand.

## 7. Title/body-e notification hardcode fa

`PageController.php:52-53` — `'پیام تماس جدید'` va matn-e body translate nistand; dar site-e en ham farsi ferestade mishavad. Data-e JSON (`name/email`) kholase-e mohavareh ast.

## 8. Form-e site hameshe be canonical route POST mishavad

`_contact.blade.php:40` — `route('site.contact.send')` (bedoon locale). Az safhe-ye `{locale}/page/contact` POST be `/contact` miravad. `back()` (`PageController.php:59`) be safhe-ye qabli (locale-dar) barmigardad pas UX shekast-e nayafte; vali agarkeh kasi form-ra dar `{locale}` copy konad va URL-ya route-e locale ra bedahad, notification/link-he mokhtalef mishavand.

## 9. Migration bedoon index

`2026_08_11_000001...:11-20` — faghat PK. Sort-e `orderByRaw('read_at IS NOT NULL, created_at DESC')` (`Admin/ContactController.php:25`) va `whereNull('read_at')` baraye table-e bozorg index mikhahand. Dar table-e koochak OK; baraye port-e high-traffic index-e `(read_at, created_at)` ezafe konid.

## 10. Do copy-e route = do limit-e throttle-e joda (conceptuali)

POST `/contact` va `{locale}/contact` har do ba `throttle:5,1` mahdood mikonand (`routes/site.php:72`, dar `routes-dump.md:380,382`) vali key-e pishfarz-e RateLimiter domain+IP ast (route name dar key nist) — amali-han yek limit-e moshtarak. Negaran nabash, vali agar `RateLimiter::for` custom besazid, in do ra yeki bedanid.

## 11. Phone-e khali dar show ba label-e «no_department» namayesh dade mishavad

`show.blade.php:71` — `{{ $contact->phone ?: __('admin.contact.no_department') }}` — key-e `admin.contact.no_department` (برای department sakhte shode) baraye phone niz estefade mishavad; label-e ghalat-e beyta.

## 12. `email` unique nist va rate taqribi match nist

`email` required+email (`PageController.php:40`) vali migration unique constraint nadarad (`:14`); yek email mitavanad 5 peyam/dagh (throttle) ferestad. Spam-filter-e ghatii nist.

## UNKNOWN

- Aya dar backlog-e team, department-haye custom (free-text) ghabel-e namayesh-e dorost dar admin bashand? (mavred-e 1)
- Aya `read_at` baraye counter/badge-e unread dar sidebar-e admin jayi digar khounde mishavad? — dar Sidebar.php:79 faghat `canAccess` check shode; counter-e unread dar sidebar peyda nashod (UNKNOWN).
