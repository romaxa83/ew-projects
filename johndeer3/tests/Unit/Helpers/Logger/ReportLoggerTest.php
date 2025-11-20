<?php

namespace Tests\Unit\Helpers\Logger;

use App\Helpers\Logger\ReportLogger;
use Tests\TestCase;

class ReportLoggerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_report_logger_info(): void
    {
        \Config::set('logging.custom.report.path', storage_path('logs/report-test.log'));
        $path = config('logging.custom.report.path');

        $this->assertFalse(file_exists($path));

        ReportLogger::info("some message");

        $this->assertTrue(
            str_contains(
                file_get_contents($path),
                'REPORT_.INFO: some message [] []'
            )
        );

        ReportLogger::info("some message 1", ['key' => 'value']);

        $this->assertTrue(
            str_contains(
                file_get_contents($path),
                'REPORT_.INFO: some message 1 {"key":"value"} []'
            )
        );

        ReportLogger::error("some error");

        $this->assertTrue(
            str_contains(
                file_get_contents($path),
                'REPORT_.ERROR: some error'
            )
        );

        $this->assertTrue(file_exists($path));

        unlink($path);
    }
}
