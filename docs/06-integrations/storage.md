# Storage Integration

> **Source:** `config/filesystems.php`, `config/paths.php`, `app/Helpers/ImageHandler.php`, `ImageUploader.php`, `ImageResizer.php`, `Uploader.php`, `VideoHandler.php`, `SettingService.php:handleFileUpload`

## Disks
- Default `local` -> `storage/app/private`
- Public `local` -> `storage/app/public` + `public/storage` link
- Upload path config `paths.upload = 'uploads'` -> `public/uploads/{folder}/`
- No S3 — all local.

## Upload logic
`SettingService::handleFileUpload(key, UploadedFile, type)`:
- if mime starts with image/ -> `ImageHandler::handle('settings', file)` -> saves to `public/uploads/settings/` + WebP conversion via GD `imagecreatefromjpeg/png -> imagepalettetotruecolor -> imagewebp(90)` -> returns webpName, deletes original; if svg/webp/gif skip convert.
- else -> `Uploader::upload(file, paths.upload.'/settings')` plain move with md5(microtime).ext
- On success deletes oldPath via `ImageHandler::delete`.

Other helpers:
- `ImageUploader::upload(folder, file)` similar but with mkdir 0755
- `ImageResizer` for resizing (not used in SettingService)
- `VideoHandler/VideoUploader` similar for videos.

## AI gotcha
- Images become WebP (except svg/gif) — check extension.
- Paths are public, not storage symlink for settings.
- No queue for image processing — synchronous GD.
