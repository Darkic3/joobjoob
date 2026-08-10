<?php

namespace Tests;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * حافظهٔ درخواستی SettingService بین تست‌ها (که در یک پروسه اجرا می‌شوند) باقی
     * می‌ماند و باعث آلودگی دادهٔ تست‌های بعدی می‌شود — قبل از هر تست خودکار پاکش می‌کنیم
     * تا هیچ تستی دادهٔ تست قبلی را نبیند و نیازی به flush دستی در هر تست نباشد.
     */
    protected function setUp(): void
    {
        parent::setUp();

        app(SettingService::class)->flush();
    }
}
