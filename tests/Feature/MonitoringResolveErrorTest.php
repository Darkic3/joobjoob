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
}
