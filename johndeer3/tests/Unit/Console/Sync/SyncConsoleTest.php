<?php

namespace Tests\Unit\Console\Sync;

use App\Services\Import\ImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SyncConsoleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    // проверяем что вызваны все методы

    /** @test */
    public function success_add(): void
    {
        $service = $this->spy(ImportService::class);

        $this->artisan('jd:sync')
            ->assertExitCode(0);

        $service->shouldHaveReceived('getData')->with(ImportService::EG)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::MD)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::REGION)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::DEALER)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::CLIENT)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::TM)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::SM)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::MANUFACTURE)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::SIZE_PARAMETERS)->once();
        $service->shouldHaveReceived('getData')->with(ImportService::PRODUCT)->once();
    }
}

