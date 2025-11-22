<?php

namespace Tests\Unit\Services\Locations;

use App\Services\Excel\Excel;
use App\Services\Locations\IpRangeService;
use Tests\TestCase;

class IpRangeServiceTest extends TestCase
{
    protected IpRangeService $service;

    public function test_import(): void
    {
        Excel::fake();

        $this->service->import();

        Excel::assertImported(database_path('files/csv/us-ip2location-lite-db9.csv'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(IpRangeService::class);
    }
}
