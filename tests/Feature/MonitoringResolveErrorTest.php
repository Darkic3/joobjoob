<?php

namespace Tests\Feature;

use App\Models\SystemError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringResolveErrorTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function makeError(): SystemError
    {
        return SystemError::create([
            'level' => 'error',
            'exception_class' => 'RuntimeException',
            'message' => 'boom',
            'file' => '/tmp/x.php',
            'line' => 1,
            'trace' => ['#0 test'],
            'is_resolved' => false,
        ]);
    }

    public function test_resolve_error_works_with_empty_note(): void
    {
        // رگرسیون: note خالی (بعد از ConvertEmptyStringsToNull می‌شود null)
        // قبلاً باعث TypeError 500 در markResolved می‌شد.
        $this->actingAs($this->makeAdmin());
        $error = $this->makeError();

        $this->post(route('admin.monitoring.errors.resolve', $error->id), ['note' => ''])
            ->assertRedirect();

        $this->assertTrue($error->fresh()->is_resolved);
    }

    public function test_resolve_error_works_without_note_field(): void
    {
        $this->actingAs($this->makeAdmin());
        $error = $this->makeError();

        $this->post(route('admin.monitoring.errors.resolve', $error->id), [])
            ->assertRedirect();

        $this->assertTrue($error->fresh()->is_resolved);
    }

    public function test_resolve_error_stores_typed_note(): void
    {
        $this->actingAs($this->makeAdmin());
        $error = $this->makeError();

        $this->post(route('admin.monitoring.errors.resolve', $error->id), ['note' => 'fixed via patch'])
            ->assertRedirect();

        $fresh = $error->fresh();
        $this->assertTrue($fresh->is_resolved);
        $this->assertSame('fixed via patch', $fresh->resolved_note);
    }

    public function test_monitoring_dashboard_loads_without_500(): void
    {
        // رگرسیون خطای #2: داشبورد نباید به‌خاطر failed_jobs (ستون/جدول ناسازگار) ۵۰۰ بدهد.
        $this->actingAs($this->makeAdmin());

        $this->get(route('admin.monitoring.index'))->assertOk();
    }

    public function test_health_check_command_does_not_crash(): void
    {
        $this->artisan('monitoring:check-health')->assertOk();
    }

    public function test_error_detail_page_renders_summary_card(): void
    {
        $this->actingAs($this->makeAdmin());
        $error = $this->makeError();

        $this->get(route('admin.monitoring.errors.show', $error->id))
            ->assertOk()
            ->assertSee('خلاصه خطا', false)
            ->assertSee('copyErrorSummaryBtn', false)
            ->assertSee('SYSTEM ERROR SUMMARY', false);
    }

    private function makeErrorAt(string $message, string $datetime): SystemError
    {
        $error = $this->makeError();
        $error->forceFill([
            'message' => $message,
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ])->save();

        return $error->fresh();
    }

    public function test_errors_date_filter_with_gregorian_range(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeErrorAt('old-error-marker', '2026-09-01 10:00:00');
        $this->makeErrorAt('new-error-marker', '2026-09-14 10:00:00');

        $this->get(route('admin.monitoring.errors', ['from_date' => '2026-09-10', 'to_date' => '2026-09-15']))
            ->assertOk()
            ->assertSee('new-error-marker', false)
            ->assertDontSee('old-error-marker', false);
    }

    public function test_errors_date_filter_with_jalali_synced_datetime(): void
    {
        // خروجی سینک پیکر شمسی «Y-m-d H:i» است؛ نباید فیلتر را خراب کند (رگرسیون to_date).
        $this->actingAs($this->makeAdmin());
        $this->makeErrorAt('synced-error-marker', '2026-09-14 10:00:00');

        $this->get(route('admin.monitoring.errors', ['from_date' => '2026-09-14 00:00', 'to_date' => '2026-09-14 00:00']))
            ->assertOk()
            ->assertSee('synced-error-marker', false)
            ->assertSee('data-jdp-only-date', false);
    }

    public function test_errors_date_filter_ignores_invalid_date(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->makeErrorAt('visible-error-marker', '2026-09-14 10:00:00');

        $this->get(route('admin.monitoring.errors', ['from_date' => 'not-a-date']))
            ->assertOk()
            ->assertSee('visible-error-marker', false);
    }
}
