<?php

namespace Tests\Unit\Services\Locations;

use App\Services\Excel\Excel;
use App\Services\Locations\ZipcodeService;
use Tests\TestCase;

class ZipcodeServiceTest extends TestCase
{
    protected ZipcodeService $service;

    public function test_import(): void
    {
        Excel::fake();

        $this->service->import();

        Excel::assertImported(database_path('files/csv/uszips.csv'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(ZipcodeService::class);
    }
}
