<?php

namespace Tests\Unit\Models\Logs;

use App\Models\Logs\Log;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Log as Logger;
use Tests\TestCase;

class LogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_write_new_log_into_database()
    {
        $message = 'error message';

        $attr = [
            'channel' => 'testing',
            'message' => $message
        ];

        $this->assertDatabaseMissing(Log::TABLE, $attr);

        Logger::error($message);

        $this->assertDatabaseHas(Log::TABLE, $attr);
    }
}
