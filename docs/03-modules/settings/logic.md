# Settings — Logic (Verified Audit)

> **Module:** Grouped config system with unified cache + static request memory
> **Service:** `SettingService` (155 lines) | **Model:** `Setting` (80 lines)
> **Helper:** `app/Helpers/settings.php` — `setting()`, `settingOverride()`, `trans_or()`
> **Controller:** `Admin/SettingController` (58 lines) | **Request:** `UpdateSettingsRequest`
> **Config:** `config/settings.php` — 14 tabs, 50+ fields
> **Migration:** `2026_07_25_094140_create_settings_table` (key unique+indexed, group indexed)

---

## 1. Purpose & Architecture

Key-value config system grouped into tabs. Admin edits settings via tabbed UI; site reads via `setting()` helper with two-layer cache (static request memory + `Cache::rememberForever`). File uploads (images → WebP, non-images → raw) stored in `public/uploads/settings/`.

**Key design decisions:**
- Single unified cache key `shopora_settings_all` (all settings in one query + one cache entry)
- Static `$memory` array prevents duplicate queries within a request
- `settingOverride()` bypasses config defaults for translatable strings
- File uploads use `ImageHandler` (images) or `Uploader` (non-images) — never raw `store()`

---

## 2. Config Schema (`config/settings.php`)

### 14 Tabs
| Tab | Label | Purpose |
|-----|-------|---------|
| `general` | عمومی | Site name, slogan, logo, favicon |
| `sms` | پیامک | Kavenegar API key, sender, OTP template |
| `home` | صفحه اصلی | Slider, deals, categories, featured, best sellers, reviews, blogs titles |
| `home_about` | صفحه اصلی - درباره | Home about section |
| `feature_home` | ویژگی‌ها | Feature highlights |
| `announce` | نوار اعلان | 3 announcement bar texts |
| `newsletter` | خبرنامه | Newsletter section |
| `socials` | شبکه‌های اجتماعی | Instagram, Telegram, WhatsApp, Twitter, YouTube |
| `contact` | تماس با ما | Email, phone, mobile, address, working hours |
| `about` | درباره ما | About title, description, company history |
| `footer` | فوتر | Footer description, copyright, newsletter title |
| `pages` | عناوین صفحات | Page titles |
| `appearance` | ظاهر و تم | Festive mode, style, badge text |
| `smart_notifications` | اعلانات هوشمند | `allow_open_requests`, `enable_ai` — **DEFAULT OFF, future feature** |

### Field Types
- `text` — single-line input (default)
- `textarea` — multi-line (some with `ckeditor: true`)
- `file` — image upload → WebP via ImageHandler
- `video` — video upload via Uploader
- `boolean` — on/off toggle
- `select` — dropdown with `options` array

### Field Structure
```php
'key' => [
    'label' => 'برچسب',
    'type' => 'text|textarea|file|video|boolean|select',
    'tab' => 'group_name',
    'required' => bool,
    'default' => mixed,
    'options' => array, // for select
    'guide' => string,  // help text
    'validation' => string, // Laravel validation rules
    'ckeditor' => bool, // for textarea
    'preview' => bool, // for select preview
]
```

---

## 3. Service — `SettingService`

### 3.1 Cache Architecture (Two-Layer)

**Layer 1: Static request memory** (`self::$memory`)
- Populated once per request via `all()` method
- Prevents duplicate DB queries within a single request
- Reset on `flush()`

**Layer 2: Unified cache** (`Cache::rememberForever('shopora_settings_all')`)
- All settings loaded in ONE query: `Setting::all()->mapWithKeys()`
- Cast per type: `json → json_decode`, `boolean → (bool)`, `select/text → (string)`
- Flushed on `updateGroup()` via `flush()`

### 3.2 `get(string $key, mixed $default): mixed`
1. Call `$this->all()` (hits memory → cache → DB)
2. If key exists in result → return value
3. Else → `getDefaultFromConfig($key)` (from `config("settings.fields.{$key}.default")`)
4. Else → return `$default` argument

### 3.3 `getStored(string $key, mixed $default): mixed`
Like `get()` but **ignores config defaults** — returns only if admin explicitly saved a non-empty value. Used by `settingOverride()` for translatable strings where config default is language-specific.

### 3.4 `updateGroup(string $group, array $data, array $files): void`
1. `DB::transaction()` — all settings in group updated atomically
2. For each field in group:
   - If `UploadedFile` → `handleFileUpload()` (images: ImageHandler WebP, others: Uploader)
   - If file field without new upload → preserve old value from DB
   - `Setting::updateOrCreate(['key' => $key], [...])`
3. After commit → `$this->flush()` (clears memory + cache)

### 3.5 `handleFileUpload(string $key, UploadedFile $file, string $type): string`
1. Get old path from DB: `Setting::where('key', $key)->value('value')`
2. Upload new file:
   - Images (`str_starts_with(mime, 'image/')`) → `ImageHandler::handle('settings', $file)` (WebP conversion)
   - Others → `Uploader::upload($file, config('paths.upload').'/settings')`
3. If upload failed → return old path (preserve)
4. If upload succeeded AND old path exists → `ImageHandler::delete('settings', $oldPath)`
5. Return new path

### 3.6 `flush(): void`
```php
self::$memory = null;           // clear request memory
Cache::forget('shopora_settings_all'); // clear unified cache
```

---

## 4. Helper Functions (`app/Helpers/settings.php`)

### `setting(string $key, mixed $default = null): mixed`
Global helper → `app(SettingService::class)->get($key, $default)`. Used everywhere in views/controllers.

### `settingOverride(string $key, mixed $default = null): mixed`
Global helper → `app(SettingService::class)->getStored($key, $default)`. Ignores config defaults — for translatable strings.

### `trans_or(string $key, ?string $fallback = null): string`
Translation with fallback to config label. If key not in lang files → return fallback or key itself.

---

## 5. Model — `Setting`

**Fillable:** `key, value, type, group, label, options, sort`
**Casts:** `options → array`, `sort → integer`
**No SoftDeletes** — hard delete only

### Static Helpers
- `getValue(string $key, mixed $default)` — direct DB query (bypasses cache!)
- `imageFile(string $key): ?string` — `asset('uploads/settings/'.$value)` with `file_exists` check
- `videoFile(string $key): ?string` — same pattern as `imageFile`

---

## 6. Controller — `Admin/SettingController`

### `edit(string $group = 'general'): View`
1. Validate group exists in `config('settings.tabs')` → 404 if not
2. Load ALL groups' values: `foreach tabs → getGroup($tab)` (for tab switching without refresh)
3. Group fields by tab: `foreach fields → fieldsByTab[tab][key] = field`
4. Render `admin.settings.edit` with `activeGroup, tabs, fieldsByTab, groupsValues`

### `update(UpdateSettingsRequest $request, string $group): Redirect`
1. `$validatedData = $request->validated()['settings'] ?? []`
2. `$files = $request->file('settings') ?? []`
3. `$this->settingService->updateGroup($group, $validatedData, $files)`
4. Redirect with success flash

---

## 7. Validation — `UpdateSettingsRequest`

Dynamic validation per group — reads `config('settings.fields')` and builds rules:
- For each field in group: use `$field['validation'] ?? 'nullable'`
- If file field AND file present → keep file rules
- If file field AND no file → strip file rules (`required`, `image`, `mimes:*`, `max:*`)
- Custom messages: `site_name.required`, `site_logo.image`

---

## 8. Routes

```
GET  /admin/settings/{group?}  → SettingController@edit   (name: admin.settings.edit)
PUT  /admin/settings/{group}   → SettingController@update  (name: admin.settings.update)
```

Middleware: `auth, adminPermission, log.admin`
No Site routes — settings are admin-only.

---

## 9. Business Rules

| Rule | Evidence |
|------|----------|
| Single cache key for all settings | `SettingService::CACHE_ALL = 'shopora_settings_all'` |
| Request memory prevents N queries | `self::$memory` static property |
| File upload: images → WebP | `handleFileUpload` checks `str_starts_with(mime, 'image/')` |
| File upload: preserve old on no-new | `elseif ($isFileType) → $value = Setting::where('key', $key)->value('value')` |
| Config defaults override argument | `get()` checks `getDefaultFromConfig()` before `$default` |
| `settingOverride` ignores config | `getStored()` returns only explicitly saved values |
| No SoftDeletes | Settings table hard delete only |
| `value` is `text` column | Stores strings, JSON encoded arrays, file paths |

---

## 10. Evidence

- `app/Services/SettingService.php` — full cache + file upload logic
- `app/Models/Setting.php` — static helpers, no SoftDeletes
- `app/Helpers/settings.php` — setting(), settingOverride(), trans_or()
- `config/settings.php` — 14 tabs, 50+ fields, smart_notifications tab
- `app/Http/Controllers/Admin/SettingController.php` — edit/update
- `app/Http/Requests/Admin/UpdateSettingsRequest.php` — dynamic validation
- `database/migrations/2026_07_25_094140_create_settings_table.php` — schema
- `routes/admin.php:58-59` — 2 routes (edit + update)
- `resources/views/layouts/site/app.blade.php:90` — `allow_open_requests` consumed
