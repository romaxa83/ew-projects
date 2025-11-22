<?php

namespace Tests\Unit\Commands\GPS;

use App\Console\Commands\GPS\ProcessMessages;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcessMessagesTest extends TestCase
{
    use DatabaseTransactions;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    public function test_deleted_orders_delete(): void
    {
        $this->artisan(ProcessMessages::class)
            ->assertExitCode(0);
    }
}
