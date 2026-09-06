<?php

namespace Tests;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * حافظهٔ درخواستی SettingService بین تست‌ها (که در یک پروسه اجرا می‌شوند) باقی
     * می‌ماند و باعث آلودگی دادهٔ تست‌های بعدی می‌شود — قبل از هر تست خودکار پاکش می‌کنیم
     * تا هیچ تستی دادهٔ تست قبلی را نبیند و نیازی به flush دستی در هر تست نباشد.
     */
    protected function setUp(): void
    {
        $this->guardTestDatabase();

        parent::setUp();

        $this->assertTestDatabase();

        app(SettingService::class)->flush();
    }

    /**
     * ایمنی دیتابیس — قبل از هر کاری (حتی قبل از migrate:fresh ترِیت RefreshDatabase)
     * مطمئن می‌شویم تست‌ها هرگز به دیتابیس واقعی پروژه (shopora) وصل نمی‌شوند.
     *
     * اگر bootstrap/cache/config.php وجود داشته باشد، Laravel کانفیگ را از همان کش
     * می‌خواند و مقدارهای phpunit.xml (مثل DB_DATABASE=shopora_test) را نادیده می‌گیرد —
     * در آن حالت migrate:fresh تست‌ها می‌تواند دیتابیس واقعی را خالی کند. پس تست را متوقف می‌کنیم.
     */
    protected function guardTestDatabase(): void
    {
        $cacheFile = dirname(__DIR__).'/bootstrap/cache/config.php';
        if (is_file($cacheFile)) {
            throw new RuntimeException(
                'bootstrap/cache/config.php exists: it makes tests use the cached (non-test) database '.
                'and ignore phpunit.xml overrides. Run "php artisan config:clear" before running tests.'
            );
        }

        $db = (string) env('DB_DATABASE', '');
        if ($db !== '' && ! str_ends_with($db, '_test')) {
            throw new RuntimeException(
                "Refusing to run tests against database '{$db}'. phpunit.xml DB_DATABASE must point to a *_test database."
            );
        }
    }

    /**
     * بررسی نهایی بعد از بوت اپ — نام دیتابیسِ حل‌شدهٔ اتصال پیش‌فرض باید *_test باشد.
     * (گارد اولیه قبل از بوتِ اپ نمی‌تواند مقدار حل‌شده را ببیند.)
     */
    protected function assertTestDatabase(): void
    {
        $connection = (string) config('database.default');
        $db = (string) config("database.connections.{$connection}.database", '');

        if ($db !== '' && ! str_ends_with($db, '_test')) {
            throw new RuntimeException(
                "Resolved test database '{$db}' is not a *_test database — aborting to protect project data."
            );
        }
    }
}
