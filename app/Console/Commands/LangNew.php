<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * ساخت پوشه‌ی ترجمه برای یک زبان جدید.
 *
 * کارها:
 *  ۱. اعتبارسنجی کد زبان (^[a-z]{2,8}$)
 *  ۲. ساخت resources/lang/{code}
 *  ۳. کپی فایل‌های ترجمه از زبان مبنا (پیش‌فرض fa، با --from)
 *  ۴. خروجی راهنما
 *
 * ترجمه‌ی رشته‌ها خودکار نیست — این کامند فقط اسکلت را می‌سازد.
 */
class LangNew extends Command
{
    protected $signature = 'lang:new {code : کد زبان، مثلاً de یا ru} {--from=fa : زبان مبدأ برای کپی فایل‌های ترجمه}';

    protected $description = 'پوشه‌ی ترجمه برای یک زبان جدید بساز (کپی از زبان مبدأ)';

    public function handle(): int
    {
        $code = strtolower((string) $this->argument('code'));
        $from = strtolower((string) $this->option('from'));

        if (! preg_match('/^[a-z]{2,8}$/', $code)) {
            $this->error("کد زبان «{$code}» نامعتبر است — فقط حروف کوچک a-z (۲ تا ۸ کاراکتر).");

            return self::FAILURE;
        }

        $langDir = resource_path('lang');
        $target = $langDir.DIRECTORY_SEPARATOR.$code;
        $source = $langDir.DIRECTORY_SEPARATOR.$from;

        if (! is_dir($source)) {
            $this->error("زبان مبدأ «{$from}» وجود ندارد (پوشه‌ی resources/lang/{$from} پیدا نشد).");

            return self::FAILURE;
        }

        if (is_dir($target)) {
            $this->warn("پوشه‌ی resources/lang/{$code} از قبل وجود دارد — بدون تغییر ماند.");

            return self::SUCCESS;
        }

        File::makeDirectory($target, 0755, true);

        // کپی همه‌ی فایل‌های php زبان مبدأ
        $copied = 0;
        foreach (File::glob($source.DIRECTORY_SEPARATOR.'*.php') as $file) {
            File::copy($file, $target.DIRECTORY_SEPARATOR.basename($file));
            $copied++;
        }

        // منبع بدون فایل ترجمه → پوشه‌ی خالی بی‌فایده است؛ برش گردان
        if ($copied === 0) {
            File::deleteDirectory($target);
            $this->error("در منابع/lang/{$from} هیچ فایل ترجمه‌ای یافت نشد — پوشه‌ی {$code} ساخته نشد.");

            return self::FAILURE;
        }

        $this->info("پوشه‌ی resources/lang/{$code} ساخته شد ({$copied} فایل کپی شد از {$from}).");
        $this->comment('حالا فایل‌ها را ترجمه کنید. سپس زبان را از پنل ادمین → Languages فعال کنید.');

        return self::SUCCESS;
    }
}
