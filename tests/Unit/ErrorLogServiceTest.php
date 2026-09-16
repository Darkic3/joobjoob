<?php

namespace Tests\Unit;

use App\Services\ErrorLogService;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\TestCase;

class ErrorLogServiceTest extends TestCase
{
    public function test_console_usage_errors_are_ignored(): void
    {
        // تایپو در tinker (مثل ارور «unexpected '=' on line 1»)
        $this->assertTrue(
            ErrorLogService::shouldIgnore(new \Psy\Exception\ParseErrorException("Syntax error, unexpected '='"))
        );

        // کامند/آپشن اشتباه artisan
        $this->assertTrue(
            ErrorLogService::shouldIgnore(new CommandNotFoundException('Command "lang:check" is not defined.'))
        );
        $this->assertTrue(
            ErrorLogService::shouldIgnore(
                new \Symfony\Component\Console\Exception\RuntimeException('The "--foo" option does not exist.')
            )
        );
    }

    public function test_real_errors_are_not_ignored(): void
    {
        $this->assertFalse(ErrorLogService::shouldIgnore(new \RuntimeException('boom')));
        $this->assertFalse(ErrorLogService::shouldIgnore(new \LogicException('Route is not bound.')));
    }
}
