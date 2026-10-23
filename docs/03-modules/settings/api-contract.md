# Settings — API Contract (Verified Audit)

> **Source:** `routes/admin.php:58-59`

---

## Admin Routes (2 routes)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | `/admin/settings/{group?}` | `admin.settings.edit` | auth, adminPermission, log.admin | `SettingController@edit` | admin |
| PUT | `/admin/settings/{group}` | `admin.settings.update` | auth, adminPermission, log.admin | `SettingController@update` | admin |

**Notes:**
- `{group}` is optional on GET (defaults to `'general'`)
- `{group}` is required on PUT
- No Site routes — settings are admin-only
- No CRUD resource pattern — just edit + update

---

## Request/Response Patterns

### GET `/admin/settings/{group}`
Renders tabbed admin form with all groups' values pre-loaded.

**Query params:** None (group is route param)

**View data:**
```php
[
    'activeGroup' => 'general',        // current tab
    'tabs' => [...],                    // 14 tabs from config
    'fieldsByTab' => [...],            // fields grouped by tab
    'groupsValues' => [...],           // all groups' values (for tab switching)
]
```

### PUT `/admin/settings/{group}`
Updates all settings in the group.

**Request body (multipart/form-data):**
```
settings[site_name] = "فروشگاه جوب‌جوب"
settings[site_slogan] = "خرید آسان"
settings[logo] = (file)              // optional
```

**Response:** Redirect to `admin.settings.edit` with success flash

**Validation:** Dynamic per group — reads `config('settings.fields')` and builds rules

---

## Validation Rules (Dynamic)

For each field in the group:
- If `$field['validation']` defined → use it
- If file field AND file present → keep file rules
- If file field AND no file → strip file rules, use `nullable`
- Default: `'nullable'`

**Custom messages:**
```php
'settings.site_name.required' => 'validation.requests.settings.site_name_required'
'settings.site_logo.image' => 'validation.requests.settings.site_logo_image'
```

---

## File Upload

**Images (logo, favicon):**
- MIME check: `str_starts_with($file->getMimeType(), 'image/')`
- Upload via `ImageHandler::handle('settings', $file)` → WebP conversion
- Stored in: `public/uploads/settings/`
- Old file deleted after successful upload

**Non-images:**
- Upload via `Uploader::upload($file, config('paths.upload').'/settings')`
- Stored in: `public/uploads/settings/`

---

## Notes

- No throttling on settings routes (admin-only, authenticated)
- No CSRF exemption — standard Laravel web middleware
- Settings are read-only from site side via `setting()` helper (no API)
