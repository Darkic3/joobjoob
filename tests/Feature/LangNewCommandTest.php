<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LangNewCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // پوشه‌های ساخته‌شده در تست را پاک کن
        File::deleteDirectory(resource_path('lang/de'));
        File::deleteDirectory(resource_path('lang/empty-src'));

        parent::tearDown();
    }

    public function test_command_creates_folder_from_base_language(): void
    {
        $this->artisan('lang:new', ['code' => 'de'])
            ->expectsOutputToContain('resources/lang/de ساخته شد')
            ->assertExitCode(0);

        $this->assertDirectoryExists(resource_path('lang/de'));
        $this->assertFileExists(resource_path('lang/de/admin.php'));
        $this->assertFileExists(resource_path('lang/de/messages.php'));
    }

    public function test_command_copies_files_from_custom_source(): void
    {
        $this->artisan('lang:new', ['code' => 'de', '--from' => 'en'])
            ->assertExitCode(0);

        // en هم admin.php دارد — کپی از آن باید همین فایل‌ها را بسازد
        $this->assertFileExists(resource_path('lang/de/admin.php'));
    }

    public function test_command_rejects_invalid_code(): void
    {
        $this->artisan('lang:new', ['code' => 'D3'])
            ->expectsOutputToContain('نامعتبر')
            ->assertExitCode(1);

        $this->assertDirectoryDoesNotExist(resource_path('lang/D3'));
    }

    public function test_command_fails_when_source_missing(): void
    {
        $this->artisan('lang:new', ['code' => 'de', '--from' => 'zz'])
            ->expectsOutputToContain('وجود ندارد')
            ->assertExitCode(1);
    }

    public function test_command_warns_when_folder_exists(): void
    {
        File::makeDirectory(resource_path('lang/de'), 0755, true);
        File::put(resource_path('lang/de/messages.php'), '<?php return [];');

        $this->artisan('lang:new', ['code' => 'de'])
            ->expectsOutputToContain('از قبل وجود دارد')
            ->assertExitCode(0);

        // محتوا دست‌نخورده بماند
        $this->assertFileExists(resource_path('lang/de/messages.php'));
    }

    public function test_command_does_not_create_empty_folder(): void
    {
        // یک پوشه‌ی منبع خالی بساز
        File::makeDirectory(resource_path('lang/empty-src'), 0755, true);

        $this->artisan('lang:new', ['code' => 'de', '--from' => 'empty-src'])
            ->expectsOutputToContain('فایل ترجمه‌ای یافت نشد')
            ->assertExitCode(1);

        $this->assertDirectoryDoesNotExist(resource_path('lang/de'));
    }
}
