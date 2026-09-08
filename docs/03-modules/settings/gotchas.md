# Settings — Gotchas & Known Bugs (Verified Audit)

---

## 1. `Setting::getValue()` bypasses cache — inconsistency (BUG)

**Bug:** `Setting::getValue()` does direct DB query (`Setting::where('key', $key)->first()`), bypassing both the static `$memory` and `Cache::rememberForever`. Meanwhile `setting()` helper uses `SettingService::get()` which respects cache.

**Impact:** If admin updates a setting, `setting()` returns new value (after flush), but `Setting::getValue()` may return stale data if called before the cache is rebuilt. Also: two different code paths for the same data.

**Evidence:** `Setting.php:22-30` vs `SettingService.php:22-38`

**Fix:** Deprecate `Setting::getValue()` — use `setting()` helper everywhere. Or make it read from `SettingService::all()`.

---

## 2. File upload — no MIME/type validation in Request (SECURITY)

**Bug:** `UpdateSettingsRequest` uses `$field['validation'] ?? 'nullable'` for file fields. Most file fields in `config/settings.php` (logo, favicon) don't define a `validation` key → they get `'nullable'` only → **no `image`, `mimes`, or `max` rules**.

**Impact:** Admin can upload any file type (PHP, SVG with JS, etc.) to `public/uploads/settings/`. If server misconfigures MIME types, this is a code execution risk.

**Evidence:** `config/settings.php` — `logo` and `favicon` have no `validation` key. `UpdateSettingsRequest.php:25-35` strips file rules when no file present.

**Fix:** Add default file validation in `UpdateSettingsRequest`: `image|mimes:jpg,jpeg,png,webp|max:2048` for `file` type fields.

---

## 3. `handleFileUpload` — old file deleted AFTER new upload (safe but fragile)

**Bug:** Upload order is: (1) upload new, (2) delete old. If step 1 succeeds but step 2 fails (permission, concurrent access), old file leaks. If step 1 fails → old file preserved (correct).

**Impact:** Minor — file leaks are possible but not critical. The reverse order (delete first, then upload) would be catastrophic if upload fails.

**Evidence:** `SettingService.php:100-118`

**Status:** Current order is the safer choice. Document but don't change.

---

## 4. Cache flush is post-transaction — race window (EDGE)

**Bug:** `updateGroup()` runs inside `DB::transaction()`, then calls `flush()` AFTER commit. Between commit and flush, another request could read stale cache.

**Impact:** Minimal in practice (milliseconds window), but theoretically possible under high concurrency.

**Evidence:** `SettingService.php:86-94` — flush after transaction

**Fix:** Use `Cache::forget()` inside the transaction (before commit) + clear memory after. Or accept the race as negligible.

---

## 5. `castValue` — `boolean` cast on string `'0'` returns `false` (correct) but `'false'` returns `true` (BUG)

**Bug:** `(bool) 'false'` → `true` in PHP. If admin types "false" in a boolean field, it's stored as string `'false'`, then cast to `true`.

**Impact:** Low — admin UI uses toggle, not text input. But direct DB edit or API could trigger.

**Evidence:** `SettingService.php:133` — `(bool) $value`

**Fix:** `castValue` should check `in_array(strtolower($value), ['0', 'false', 'no', ''], true)` for false.

---

## 6. No SoftDeletes — settings can't be恢复

**Bug:** Settings table has no `SoftDeletes`. If a setting is accidentally deleted (via DB or future API), it's gone. Config defaults still work via `getDefaultFromConfig()`, but explicit admin choices are lost.

**Impact:** Medium — recovery requires DB backup.

**Evidence:** `migration:2026_07_25_094140` — no `$table->softDeletes()`

---

## 7. `smart_notifications` tab — feature toggle exists but implementation incomplete

**Status:** `allow_open_requests` and `enable_ai` are defined in config with `default: false` and `guide: "فعلاً غیرفعال — برای پیاده‌سازی آینده"`.

**Current usage:** Only `allow_open_requests` is consumed in `layouts/site/app.blade.php:90` (`@if(setting('allow_open_requests', false))`). `enable_ai` has zero consumers.

**Impact:** The UI toggle exists in admin panel but does nothing for `enable_ai`. `allow_open_requests` controls a Blade section.

**Fix:** Either implement the features or remove the config entries until ready.

---

## 8. `trans_or()` helper — potential infinite recursion if fallback is itself

**Bug:** `trans_or(string $key, ?string $fallback = null)` → if `$fallback` is null, returns `$key`. If `$key` is a translation key like `notifications.order.created.title`, it returns the raw key string (which is the same bug as Notifications gotcha #1).

**Impact:** Cosmetic — raw keys shown to users.

**Evidence:** `app/Helpers/settings.php:26`

---

## 9. No `sort` field usage in admin UI

**Bug:** Settings table has `sort` column (integer, default 0), and it's in `$fillable`. But `SettingController::edit()` and `SettingService::getGroup()` don't order by `sort` — they use config field order. The column is vestigial.

**Impact:** None currently — but misleading for future developers.

---

## 10. `options` JSON column — no validation of select values

**Bug:** `select` fields store `options` as JSON array. No validation that the stored value is one of the defined options. Admin could manually set a value not in the options list.

**Impact:** Low — UI enforces selection, but direct DB edit bypasses.

---

## AI Checklist before coding Settings

- [ ] Used `setting()` helper — NOT `Setting::getValue()` (bypasses cache)
- [ ] Used `settingOverride()` for translatable strings (ignores config defaults)
- [ ] Called `SettingService::flush()` after any direct DB write to settings
- [ ] Added file validation rules in config for file-type fields
- [ ] Checked `config('settings.tabs')` for valid group name before edit
- [ ] Remembered: no SoftDeletes — hard delete only
- [ ] Remembered: `value` column is `text` — store JSON as `json_encode()`
- [ ] Smart Notifications toggle: `default: false` — document before enabling

## Evidence

- `app/Services/SettingService.php` — cache architecture, file upload, flush
- `app/Models/Setting.php` — getValue bypasses cache, imageFile/videoFile helpers
- `app/Helpers/settings.php` — setting(), settingOverride(), trans_or()
- `config/settings.php` — 14 tabs, 50+ fields, smart_notifications
- `app/Http/Controllers/Admin/SettingController.php` — edit/update
- `app/Http/Requests/Admin/UpdateSettingsRequest.php` — dynamic validation
- `database/migrations/2026_07_25_094140_create_settings_table.php` — schema
- `resources/views/layouts/site/app.blade.php:90` — allow_open_requests consumed
