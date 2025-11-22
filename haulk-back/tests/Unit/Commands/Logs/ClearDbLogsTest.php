<?php

namespace Tests\Unit\Commands\Logs;

use App\Console\Commands\Logs\ClearDbLogs;
use App\Models\Logs\Log;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ClearDbLogsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        Log::query()->delete();
    }

    public function test_delete_old_logs()
    {
        $this->assertDatabaseCount(Log::TABLE, 0);

        $days = 5;
        Config::set('history.logs.keep', $days);

        $older = now()->subDays($days + 1)->getTimestamp();
        factory(Log::class)
            ->times(10)
            ->create(['unix_time' => $older]);

        $newer = now()->subDays($days - 1)->getTimestamp();
        factory(Log::class)
            ->times(10)
            ->create(['unix_time' => $newer]);

        $this->artisan(ClearDbLogs::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount(Log::TABLE, 10);
    }
}
