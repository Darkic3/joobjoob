# Storage Integration

> **Source:** `config/filesystems.php`, `config/paths.php`, `app/Helpers/ImageHandler.php`, `ImageUploader.php`, `ImageResizer.php`, `Uploader.php`, `VideoHandler.php`, `SettingService.php:handleFileUpload`
> **Audit:** 2026-09-07 — Pure Audit, hich code fix nashod.

## Disks

- Default `local` -> `storage/app` (`config/filesystems.php:35`)
- Public `public` -> `storage/app/public` + `public/storage` symlink (`config/filesystems.php:39-45,72-74`) — `php artisan storage:link`
- Upload path config `paths.upload = 'uploads'` (`config/paths.php:12`, env `UPLOAD_PATH`) -> `public/uploads/{folder}/` — **mostaghim public_path, na Storage disk**
- No S3 — all local (`filesystems.php:47` s3 config mojood ama estefade nemishavad).

> **Tafkik-e mohem:** Do system joda: (1) `Storage::disk('public')` -> `storage/app/public` via symlink, (2) `Uploader/ImageHandler` -> `public/uploads/{folder}/` direct. Code-e feli **(2) ra estefade mikonad** — symlink baraye upload haye product/settings aslan darkil nist.

## Upload logic
`SettingService::handleFileUpload(key, UploadedFile, type)`:
- if mime starts with image/ -> `ImageHandler::handle('settings', file)` -> saves to `public/uploads/settings/` + WebP conversion via GD `imagecreatefromjpeg/png -> imagepalettetotruecolor -> imagewebp(90)` -> returns webpName, deletes original; if svg/webp/gif skip convert.
- else -> `Uploader::upload(file, paths.upload.'/settings')` plain move with md5(microtime).ext
- On success deletes oldPath via `ImageHandler::delete`.

Other helpers:
- `ImageUploader::upload(folder, file)` similar but with mkdir 0755
- `ImageResizer` for resizing (not used in SettingService)
- `VideoHandler/VideoUploader` similar for videos.

## Verified Paths (2026-09-07 — `ls` host)

```
public/storage -> /f/Projects/joobjoob/storage/app/public  (symlink mojood)
public/uploads/ -> 9 folder: bank, blog-categories, blogs, brands, hero-slides, product-categories, products, receipts, settings
```

> Natije: `storage:link` ghablan ejra shode ama upload haye product/settings dar `public/uploads/` hastand — backup-e deploy bayad `public/uploads/` ra poshesh dahad, na `storage/app/public`.

## AI gotcha
- Images become WebP (except svg/gif) — check extension.
- Paths are public, not storage symlink for settings.
- No queue for image processing — synchronous GD.
- Deploy checklist: `public/uploads/` ro backup kon + permission 775 — storage:link faaliat-e upload ra taghir nemidahad.
